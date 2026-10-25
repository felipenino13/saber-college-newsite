import csv
import html
import json
import statistics
from collections import defaultdict
from pathlib import Path
from urllib.parse import urlparse

ROOT = Path(__file__).resolve().parent
PROJECT = ROOT.parents[1]
RAW = ROOT / "raw"

PAGE_LABELS = {
    "home": "Home",
    "programs": "Programs",
    "professional-nursing": "Professional Nursing",
    "physical-therapist-assistant": "Physical Therapist Assistant",
    "admissions-tuition": "Admissions and Tuition",
    "student-services": "Student Services",
    "general": "General",
    "lp-pnp": "Landing PNP",
    "lp-pta": "Landing PTA",
}


def load_lhr(path):
    return json.loads(path.read_text(encoding="utf-8"))


def metric(lhr, audit_id):
    return float(lhr["audits"].get(audit_id, {}).get("numericValue") or 0)


def sum_items(lhr, audit_id, field):
    items = ((lhr["audits"].get(audit_id, {}).get("details") or {}).get("items") or [])
    return sum(float(item.get(field) or 0) for item in items)


def dom_elements(lhr):
    details = lhr["audits"].get("dom-size-insight", {}).get("details") or {}
    return int((details.get("debugData") or {}).get("totalElements") or 0)


def lcp_label(lhr):
    details = lhr["audits"].get("lcp-discovery-insight", {}).get("details") or {}
    for item in details.get("items") or []:
        if item.get("type") == "node":
            return item.get("nodeLabel") or item.get("selector") or ""
    return ""


def summarize_lhrs(slug, paths):
    lhrs = [load_lhr(path) for path in paths]
    first = lhrs[0]
    audit_ids = {
        "score": lambda x: 100 * x["categories"]["performance"]["score"],
        "fcp_ms": lambda x: metric(x, "first-contentful-paint"),
        "lcp_ms": lambda x: metric(x, "largest-contentful-paint"),
        "tbt_ms": lambda x: metric(x, "total-blocking-time"),
        "cls": lambda x: metric(x, "cumulative-layout-shift"),
        "speed_index_ms": lambda x: metric(x, "speed-index"),
        "tti_ms": lambda x: metric(x, "interactive"),
        "transfer_bytes": lambda x: metric(x, "total-byte-weight"),
        "request_count": lambda x: len(x["audits"]["network-requests"]["details"]["items"]),
        "main_thread_ms": lambda x: metric(x, "mainthread-work-breakdown"),
        "server_response_ms": lambda x: metric(x, "server-response-time"),
        "dom_elements": dom_elements,
        "image_savings_bytes": lambda x: sum_items(x, "image-delivery-insight", "wastedBytes"),
        "unused_js_bytes": lambda x: sum_items(x, "unused-javascript", "wastedBytes"),
        "unused_css_bytes": lambda x: sum_items(x, "unused-css-rules", "wastedBytes"),
        "third_party_bytes": lambda x: sum_items(x, "third-parties-insight", "transferSize"),
    }
    result = {
        "slug": slug,
        "label": PAGE_LABELS[slug],
        "url": first["finalDisplayedUrl"],
        "runs": len(lhrs),
        "lighthouse_version": first["lighthouseVersion"],
        "fetch_times": [item["fetchTime"] for item in lhrs],
    }
    for key, getter in audit_ids.items():
        result[key] = statistics.median(getter(item) for item in lhrs)
    result["lcp_element"] = lcp_label(first)
    return result


mobile_groups = defaultdict(list)
for path in sorted(RAW.glob("*-mobile-run*.json")):
    name = path.stem.replace(".report", "")
    slug = name.rsplit("-mobile-run", 1)[0]
    mobile_groups[slug].append(path)

pages = [summarize_lhrs(slug, paths) for slug, paths in mobile_groups.items()]
pages.sort(key=lambda item: list(PAGE_LABELS).index(item["slug"]))
desktop = summarize_lhrs("home", [RAW / "home-desktop-run1.report.json"])

representative_lhrs = {}
for page in pages:
    candidates = mobile_groups[page["slug"]]
    if page["slug"] == "home":
        representative = sorted(candidates, key=lambda p: load_lhr(p)["categories"]["performance"]["score"])[len(candidates) // 2]
    else:
        representative = candidates[0]
    representative_lhrs[page["slug"]] = load_lhr(representative)

resources = {}
resource_use = defaultdict(set)
page_resource_types = {}
for page in pages:
    lhr = representative_lhrs[page["slug"]]
    type_totals = defaultdict(lambda: {"requests": 0, "transfer_bytes": 0})
    for item in lhr["audits"]["network-requests"]["details"]["items"]:
        url = item.get("url", "")
        size = int(item.get("transferSize") or 0)
        kind = item.get("resourceType") or "Other"
        type_totals[kind]["requests"] += 1
        type_totals[kind]["transfer_bytes"] += size
        resource_use[url].add(page["slug"])
        if url not in resources or size > resources[url]["transfer_bytes"]:
            resources[url] = {
                "url": url,
                "host": urlparse(url).netloc,
                "resource_type": kind,
                "transfer_bytes": size,
            }
    page_resource_types[page["slug"]] = dict(type_totals)

top_resources = sorted(resources.values(), key=lambda item: item["transfer_bytes"], reverse=True)
for item in top_resources:
    item["pages"] = sorted(resource_use[item["url"]])
video_bytes = next(
    (item["transfer_bytes"] for item in top_resources if "NURSING-IA-VIDEO-1.webm" in item["url"]),
    0,
)

summary = {
    "generated_date": "2026-10-18",
    "scope": "Read-only Lighthouse laboratory baseline on local WordPress. No performance changes applied.",
    "lighthouse_version": pages[0]["lighthouse_version"],
    "mobile_pages": len(pages),
    "median_mobile_score": statistics.median(page["score"] for page in pages),
    "average_mobile_score": round(statistics.mean(page["score"] for page in pages), 1),
    "home_mobile_score_median": next(page["score"] for page in pages if page["slug"] == "home"),
    "home_desktop_score": desktop["score"],
    "mobile_lcp_good": sum(page["lcp_ms"] <= 2500 for page in pages),
    "mobile_lcp_needs_improvement": sum(2500 < page["lcp_ms"] <= 4000 for page in pages),
    "mobile_lcp_poor": sum(page["lcp_ms"] > 4000 for page in pages),
    "mobile_cls_good": sum(page["cls"] <= 0.1 for page in pages),
    "mobile_tbt_good": sum(page["tbt_ms"] <= 200 for page in pages),
    "heaviest_page": max(pages, key=lambda page: page["transfer_bytes"])["slug"],
    "largest_lcp_page": max(pages, key=lambda page: page["lcp_ms"])["slug"],
}

inventory = {
    "summary": summary,
    "mobile_pages": pages,
    "home_desktop": desktop,
    "resource_types_by_page": page_resource_types,
    "largest_unique_resources": top_resources[:50],
}
(ROOT / "performance-inventory-before.json").write_text(json.dumps(inventory, indent=2, ensure_ascii=False), encoding="utf-8")

with (ROOT / "performance-page-inventory-before.csv").open("w", encoding="utf-8-sig", newline="") as handle:
    writer = csv.writer(handle)
    writer.writerow([
        "page", "url", "runs", "performance_score", "fcp_ms", "lcp_ms", "tbt_ms", "cls",
        "speed_index_ms", "transfer_kib", "requests", "main_thread_ms", "server_response_ms",
        "dom_elements", "estimated_image_savings_kib", "unused_js_kib", "unused_css_kib",
    ])
    for page in pages:
        writer.writerow([
            page["label"], page["url"], page["runs"], round(page["score"]), round(page["fcp_ms"]),
            round(page["lcp_ms"]), round(page["tbt_ms"]), round(page["cls"], 3),
            round(page["speed_index_ms"]), round(page["transfer_bytes"] / 1024), page["request_count"],
            round(page["main_thread_ms"]), round(page["server_response_ms"]), page["dom_elements"],
            round(page["image_savings_bytes"] / 1024), round(page["unused_js_bytes"] / 1024),
            round(page["unused_css_bytes"] / 1024),
        ])


def seconds(ms):
    return f"{ms / 1000:.1f} s"


def mib(value):
    return f"{value / 1024 / 1024:.1f} MB"


def page_table():
    rows = [
        "| Página | Score | FCP | LCP | TBT | CLS | Transferencia | Solicitudes |",
        "|---|---:|---:|---:|---:|---:|---:|---:|",
    ]
    for page in pages:
        rows.append(
            f'| [{page["label"]}]({page["url"]}) | {round(page["score"])} | {seconds(page["fcp_ms"])} | '
            f'{seconds(page["lcp_ms"])} | {round(page["tbt_ms"])} ms | {page["cls"]:.3f} | '
            f'{mib(page["transfer_bytes"])} | {page["request_count"]} |'
        )
    return "\n".join(rows)


def resources_table():
    rows = ["| Recurso | Tipo | Peso | Páginas medidas |", "|---|---|---:|---:|"]
    for item in top_resources[:12]:
        filename = item["url"].split("?")[0].rstrip("/").split("/")[-1] or item["url"]
        rows.append(
            f'| `{filename}` | {item["resource_type"]} | {mib(item["transfer_bytes"])} | {len(item["pages"])} |'
        )
    return "\n".join(rows)


report = f"""# Línea base de rendimiento — Lighthouse

Fecha del corte: **18 de octubre de 2026**  
Entorno: **WordPress local `http://localhost:8082/`**  
Herramienta: **Lighthouse {summary['lighthouse_version']}**  
Alcance: **auditoría de laboratorio de solo lectura; todavía no se aplicaron optimizaciones**.

## Resumen

- Score móvil mediano de las 9 rutas representativas: **{summary['median_mobile_score']:.0f}/100**; promedio: **{summary['average_mobile_score']}/100**.
- Home móvil, mediana de tres ejecuciones: **{summary['home_mobile_score_median']:.0f}/100**.
- Home escritorio: **{summary['home_desktop_score']:.0f}/100**.
- LCP móvil: **{summary['mobile_lcp_good']} bueno**, **{summary['mobile_lcp_needs_improvement']} por mejorar** y **{summary['mobile_lcp_poor']} deficientes**.
- CLS dentro del rango bueno: **{summary['mobile_cls_good']}/9**. El home queda en **0.162** por cambios de fuente, formulario y contenido del hero.
- TBT dentro del rango bueno: **{summary['mobile_tbt_good']}/9**. JavaScript no es el principal cuello de botella inicial.
- Página más pesada: **Home, {mib(next(page['transfer_bytes'] for page in pages if page['slug'] == 'home'))}**.
- LCP más lento: **Professional Nursing, {seconds(max(page['lcp_ms'] for page in pages))}**.

Estas cifras son datos simulados de laboratorio en el computador local. No sustituyen Core Web Vitals reales de usuarios ni representan exactamente la latencia del hosting de producción.

## Inventario por página

{page_table()}

## Recursos más pesados

{resources_table()}

El home carga un video WebM de aproximadamente **{mib(video_bytes)}** y cinco PNG principales de entre **1.1 y 2.6 MB**. Lighthouse estima cerca de **{mib(next(page['image_savings_bytes'] for page in pages if page['slug'] == 'home'))}** de ahorro posible solamente mediante entrega de imágenes. El hero se usa como fondo CSS, por lo que el navegador no descubre temprano el recurso LCP ni puede asignarle `fetchpriority="high"` de forma directa.

## Inventario técnico observado

- WordPress con Hello Elementor y Elementor/Pro Elements.
- **94 solicitudes** en el home móvil y aproximadamente **{mib(next(page['transfer_bytes'] for page in pages if page['slug'] == 'home'))}** transferidos.
- El home contiene aproximadamente **{int(next(page['dom_elements'] for page in pages if page['slug'] == 'home'))} nodos DOM**.
- Fuentes externas: Nunito y Nunito Sans con muchas variantes solicitadas mediante Google Fonts.
- Servicios y scripts externos observados: Trustindex, Elfsight, JSDelivr/`intl-tel-input`, Ipify e ipinfo.
- Scripts globales relevantes: jQuery, Elementor, Pro Elements, AI Engine/Chatbot y recursos del formulario telefónico.
- Oportunidad estimada en el home: **{mib(next(page['image_savings_bytes'] for page in pages if page['slug'] == 'home'))} en imágenes**, alrededor de **47 KiB de JavaScript sin usar**, **12 KiB de CSS sin usar** y hasta **620 ms** por recursos de bloqueo de renderizado.

## Lista priorizada de acciones propuestas — no ejecutadas

| Prioridad | Acción propuesta | Evidencia | Resultado esperado | Riesgo/validación |
|---|---|---|---|---|
| P0 | Convertir y redimensionar los PNG grandes del home y programas a WebP/AVIF, generando variantes responsivas | Home 15.5 MB; PNP 6.8 MB; PTA 5.9 MB; ahorro de imágenes del home cercano a 6.9 MB | Menor transferencia y LCP más rápido | Comparar calidad visual y verificar cada breakpoint en Elementor |
| P0 | Tratar el recurso LCP del hero como imagen prioritaria o precargarlo; evitar que dependa solo de un fondo CSS | Lighthouse no lo descubre en el HTML inicial y no tiene prioridad alta | Reducir especialmente los LCP de 4–13 s | Mantener el diseño y la edición nativa de Elementor |
| P0 | Evitar la descarga inicial del video `NURSING-IA-VIDEO-1.webm`; usar `preload="none"`, póster y carga al acercarse o interactuar | El archivo aporta cerca de {mib(video_bytes)} al home | Gran reducción del peso inicial | Comprobar autoplay, controles y experiencia móvil |
| P1 | Consolidar Nunito/Nunito Sans, limitar pesos usados, autoalojar WOFF2 y precargar solo la fuente crítica | Fuentes bloquean renderizado y explican parte del CLS 0.162 del home | Mejor FCP y estabilidad visual | Ajustar métricas de fallback para evitar cambios de línea |
| P1 | Reducir recursos que bloquean renderizado: CSS de widgets no usados, jQuery Migrate y estilos Elementor no críticos | Lighthouse estima hasta 620 ms en el home | Mejor FCP/LCP | Revisar formularios, menús, acordeones y editor de Elementor |
| P1 | Cargar de forma condicional o después de interacción Trustindex, Elfsight, chatbot e internacionalización telefónica | Se cargan dependencias y llamadas externas durante el arranque | Menos solicitudes y trabajo inicial | No retrasar funciones necesarias para conversión |
| P1 | Reservar dimensiones del hero, mascota y pasos del formulario; estabilizar el cambio de fuentes | CLS del home 0.162 | Llevar CLS por debajo de 0.1 | Verificar móvil, tablet y escritorio |
| P2 | Revisar page cache, object cache, OPcache, compresión y tiempo de respuesta del hosting | Documento inicial local cercano a 610 ms en la primera prueba | Mejor TTFB/FCP | Volver a medir en staging/producción; el servidor local no representa el hosting |
| P2 | Reducir anidación de contenedores y nodos repetidos en loops del home | Aproximadamente 1,055 nodos DOM | Menos trabajo de estilo/layout | Mantener la estructura editable y comparar texto/widgets |
| P2 | Revisar actualizaciones pendientes de Elementor, Rank Math, AI Engine y Trustindex en staging | Inventario de plugins muestra versiones pendientes | Posibles correcciones y mejoras acumuladas | Hacer respaldo y regresión visual/funcional; no actualizar solo por puntuación |

## Orden sugerido para la siguiente fase

1. Imágenes y video del home.
2. Imágenes y LCP de Professional Nursing, Student Services, General y Programs.
3. Descubrimiento/prioridad de los hero y corrección del CLS del home.
4. Fuentes y recursos que bloquean renderizado.
5. Scripts externos y carga condicional.
6. Caché/servidor, DOM y actualizaciones en un entorno de prueba.
7. Repetir tres ejecuciones móviles por página y comparar contra esta línea base.

## Evidencia conservada

- `raw/`: resultados JSON de todas las pruebas y reportes HTML completos del home.
- `run-manifest.json`: versión, URL, perfil y score de cada ejecución.
- `performance-inventory-before.json`: inventario consolidado por página y recurso.
- `performance-page-inventory-before.csv`: tabla reutilizable para la comparación posterior.
- `lighthouse-before-overview.html` y `.png`: fotografía visual de esta línea base.
- `active-plugins.json` y `active-theme.json`: inventario local de la plataforma.
"""
(PROJECT / "reports" / "performance-baseline-before-20261018.md").write_text(report, encoding="utf-8")


def score_class(score):
    return "good" if score >= 90 else "mid" if score >= 50 else "poor"


def lcp_class(lcp):
    return "good" if lcp <= 2500 else "mid" if lcp <= 4000 else "poor"


cards = []
for page in pages:
    cards.append(f'''<article class="page-card">
      <header><h3>{html.escape(page['label'])}</h3><strong class="score {score_class(page['score'])}">{round(page['score'])}</strong></header>
      <dl><div><dt>LCP</dt><dd class="{lcp_class(page['lcp_ms'])}">{seconds(page['lcp_ms'])}</dd></div><div><dt>FCP</dt><dd>{seconds(page['fcp_ms'])}</dd></div><div><dt>TBT</dt><dd>{round(page['tbt_ms'])} ms</dd></div><div><dt>CLS</dt><dd>{page['cls']:.3f}</dd></div><div><dt>Peso</dt><dd>{mib(page['transfer_bytes'])}</dd></div><div><dt>Requests</dt><dd>{page['request_count']}</dd></div></dl>
    </article>''')

dashboard = f'''<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lighthouse baseline</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');
:root{{--navy:#082b55;--gold:#fdc800;--blue:#25377b;--mist:#edf3f7;--line:#d8e3eb;--text:#29435f;--green:#13795b;--green-bg:#ddf5ea;--yellow:#805d00;--yellow-bg:#fff0bd;--red:#b42318;--red-bg:#fee4e2}}
*{{box-sizing:border-box}} body{{margin:0;background:#e7eef4;font-family:Nunito,Arial,sans-serif;color:var(--navy);padding:50px}} .sheet{{max-width:1480px;margin:auto;border-radius:30px;overflow:hidden;background:linear-gradient(145deg,#fff 0%,#f7fbfd 72%,#fff7df 100%);box-shadow:0 28px 70px rgba(8,43,85,.14);border:1px solid rgba(8,43,85,.08)}}
.hero{{position:relative;background:var(--navy);color:#fff;padding:45px 56px 39px;overflow:hidden}} .hero:after{{content:"";position:absolute;width:520px;height:520px;right:-280px;top:-250px;border:1px solid rgba(255,255,255,.16);border-radius:76px;transform:rotate(43deg);box-shadow:55px 55px 0 -54px rgba(253,200,0,.8)}} .eyebrow{{margin:0 0 10px;color:var(--gold);font-size:14px;font-weight:800;letter-spacing:.12em}} h1{{font-size:44px;letter-spacing:-.035em;line-height:1.05;margin:0}} .hero-copy{{margin:14px 0 0;color:rgba(255,255,255,.76);font-size:17px}} .date{{position:absolute;right:56px;bottom:40px;font-weight:800}}
.metrics{{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;padding:23px 28px;background:white;border-bottom:1px solid var(--line)}} .metric{{border:1px solid var(--line);border-radius:16px;padding:16px;background:#fff}} .metric b{{display:block;font-size:26px;line-height:1}} .metric span{{display:block;margin-top:7px;color:var(--text);font-size:12px;font-weight:700}} .metric.alert b{{color:var(--red)}}
.content{{padding:28px}} .section-title{{display:flex;align-items:end;justify-content:space-between;margin:0 0 16px}} .section-title h2{{margin:0;font-size:25px}} .section-title p{{margin:0;color:#61758a;font-size:13px;font-weight:700}} .grid{{display:grid;grid-template-columns:repeat(3,1fr);gap:15px}} .page-card{{background:#fff;border:1px solid var(--line);border-radius:18px;padding:18px;box-shadow:0 8px 22px rgba(8,43,85,.045)}} .page-card header{{display:flex;align-items:center;justify-content:space-between;gap:15px;padding-bottom:13px;border-bottom:1px solid #edf2f6}} h3{{font-size:17px;margin:0}} .score{{min-width:48px;border-radius:999px;padding:6px 9px;text-align:center;font-size:15px}} .good{{color:var(--green)!important;background:var(--green-bg)}} .mid{{color:var(--yellow)!important;background:var(--yellow-bg)}} .poor{{color:var(--red)!important;background:var(--red-bg)}} dl{{display:grid;grid-template-columns:repeat(3,1fr);gap:9px;margin:14px 0 0}} dl div{{background:#f7fafc;border-radius:10px;padding:9px}} dt{{font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:#77899a;font-weight:800}} dd{{margin:4px 0 0;font-size:14px;font-weight:800;color:var(--text)}} dd.good,dd.mid,dd.poor{{background:transparent;padding:0;border-radius:0}}
.findings{{display:grid;grid-template-columns:1.25fr 1fr 1fr;gap:14px;margin-top:18px}} .finding{{border-radius:17px;padding:18px 20px;background:#fff;border:1px solid var(--line)}} .finding strong{{display:block;font-size:22px;color:var(--red)}} .finding span{{display:block;margin-top:5px;color:var(--text);font-size:13px;font-weight:700;line-height:1.4}} .note{{margin-top:18px;background:#fff4ce;border:1px solid #efd47c;color:#634d00;border-radius:17px;padding:16px 19px;font-size:13px;font-weight:700;line-height:1.45}}
</style></head><body><main class="sheet"><header class="hero"><p class="eyebrow">SABER COLLEGE · PERFORMANCE BASELINE</p><h1>Lighthouse: estado antes de optimizar</h1><p class="hero-copy">Auditoría local de solo lectura · Perfil móvil simulado · Lighthouse {summary['lighthouse_version']}</p><span class="date">18 OCT 2026</span></header>
<section class="metrics"><div class="metric"><b>{summary['median_mobile_score']:.0f}</b><span>Mediana móvil / 100</span></div><div class="metric"><b>{summary['home_mobile_score_median']:.0f}</b><span>Home móvil / 100</span></div><div class="metric"><b>{summary['home_desktop_score']:.0f}</b><span>Home desktop / 100</span></div><div class="metric alert"><b>{summary['mobile_lcp_poor']}/9</b><span>LCP deficiente</span></div><div class="metric"><b>9/9</b><span>TBT en buen rango</span></div><div class="metric alert"><b>15.5 MB</b><span>Peso del home</span></div></section>
<section class="content"><div class="section-title"><h2>Inventario de rutas críticas</h2><p>Home usa la mediana de 3 ejecuciones</p></div><div class="grid">{''.join(cards)}</div>
<div class="findings"><div class="finding"><strong>≈ 6.9 MB</strong><span>Ahorro estimado en imágenes del home mediante formatos y tamaños adecuados.</span></div><div class="finding"><strong>13.3 s</strong><span>Mayor LCP observado: Professional Nursing.</span></div><div class="finding"><strong>{mib(video_bytes)}</strong><span>Video cargado durante el arranque del home.</span></div></div>
<div class="note">Fotografía de línea base. No se aplicaron cambios. Prioridad propuesta: imágenes y video, descubrimiento de los hero LCP, estabilidad del home, fuentes y recursos que bloquean renderizado.</div></section></main></body></html>'''
(ROOT / "lighthouse-before-overview.html").write_text(dashboard, encoding="utf-8")
print(json.dumps(summary, indent=2, ensure_ascii=False))
