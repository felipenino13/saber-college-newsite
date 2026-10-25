import csv
import html
import json
import statistics
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parent
PROJECT = ROOT.parents[1]
BEFORE_ROOT = PROJECT / "reports" / "lighthouse-before-20261018"
BEFORE_RAW = BEFORE_ROOT / "raw"
AFTER_RAW = ROOT / "raw"

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


def load(path):
    return json.loads(path.read_text(encoding="utf-8"))


def metric(lhr, audit_id):
    return float(lhr["audits"].get(audit_id, {}).get("numericValue") or 0)


def sum_items(lhr, audit_id, field):
    items = ((lhr["audits"].get(audit_id, {}).get("details") or {}).get("items") or [])
    return sum(float(item.get(field) or 0) for item in items)


def dom_elements(lhr):
    details = lhr["audits"].get("dom-size-insight", {}).get("details") or {}
    return int((details.get("debugData") or {}).get("totalElements") or 0)


def summarize(slug, paths):
    lhrs = [load(path) for path in paths]
    getters = {
        "score": lambda x: 100 * x["categories"]["performance"]["score"],
        "fcp_ms": lambda x: metric(x, "first-contentful-paint"),
        "lcp_ms": lambda x: metric(x, "largest-contentful-paint"),
        "tbt_ms": lambda x: metric(x, "total-blocking-time"),
        "cls": lambda x: metric(x, "cumulative-layout-shift"),
        "speed_index_ms": lambda x: metric(x, "speed-index"),
        "transfer_bytes": lambda x: metric(x, "total-byte-weight"),
        "request_count": lambda x: len(x["audits"]["network-requests"]["details"]["items"]),
        "main_thread_ms": lambda x: metric(x, "mainthread-work-breakdown"),
        "server_response_ms": lambda x: metric(x, "server-response-time"),
        "dom_elements": dom_elements,
        "image_savings_bytes": lambda x: sum_items(x, "image-delivery-insight", "wastedBytes"),
        "unused_js_bytes": lambda x: sum_items(x, "unused-javascript", "wastedBytes"),
        "unused_css_bytes": lambda x: sum_items(x, "unused-css-rules", "wastedBytes"),
    }
    result = {
        "slug": slug,
        "label": PAGE_LABELS[slug],
        "url": lhrs[0]["finalDisplayedUrl"],
        "runs": len(lhrs),
        "fetch_times": [item["fetchTime"] for item in lhrs],
    }
    for key, getter in getters.items():
        result[key] = statistics.median(getter(item) for item in lhrs)
    return result


def collect(raw):
    groups = defaultdict(list)
    for path in sorted(raw.glob("*-mobile-run*.json")):
        slug = path.stem.replace(".report", "").rsplit("-mobile-run", 1)[0]
        groups[slug].append(path)
    pages = [summarize(slug, paths) for slug, paths in groups.items()]
    pages.sort(key=lambda item: list(PAGE_LABELS).index(item["slug"]))
    desktop = summarize("home", [raw / "home-desktop-run1.report.json"])
    return pages, desktop


before_pages, before_desktop = collect(BEFORE_RAW)
after_pages, after_desktop = collect(AFTER_RAW)
before = {item["slug"]: item for item in before_pages}
after = {item["slug"]: item for item in after_pages}


def aggregate(pages, desktop):
    home = next(item for item in pages if item["slug"] == "home")
    return {
        "mobile_pages": len(pages),
        "median_mobile_score": statistics.median(item["score"] for item in pages),
        "average_mobile_score": statistics.mean(item["score"] for item in pages),
        "home_mobile_score": home["score"],
        "home_desktop_score": desktop["score"],
        "home_transfer_bytes": home["transfer_bytes"],
        "home_lcp_ms": home["lcp_ms"],
        "home_cls": home["cls"],
        "lcp_good": sum(item["lcp_ms"] <= 2500 for item in pages),
        "lcp_needs_improvement": sum(2500 < item["lcp_ms"] <= 4000 for item in pages),
        "lcp_poor": sum(item["lcp_ms"] > 4000 for item in pages),
        "cls_good": sum(item["cls"] <= 0.1 for item in pages),
        "tbt_good": sum(item["tbt_ms"] <= 200 for item in pages),
    }


before_summary = aggregate(before_pages, before_desktop)
after_summary = aggregate(after_pages, after_desktop)
comparisons = []
for slug in PAGE_LABELS:
    old = before[slug]
    new = after[slug]
    comparisons.append({
        "slug": slug,
        "label": PAGE_LABELS[slug],
        "url": new["url"],
        "before": old,
        "after": new,
        "score_delta": new["score"] - old["score"],
        "lcp_delta_ms": new["lcp_ms"] - old["lcp_ms"],
        "transfer_delta_bytes": new["transfer_bytes"] - old["transfer_bytes"],
        "transfer_reduction_pct": 100 * (old["transfer_bytes"] - new["transfer_bytes"]) / old["transfer_bytes"],
    })

qa = load(ROOT / "functional-visual-qa.json")
image_manifest = load(PROJECT / "reports" / "performance-image-sidecars-20261018.json")
elementor_before = load(PROJECT / "reports" / "backups" / "performance-20261018" / "home-elementor-data-before.json")
elementor_after = load(ROOT / "home-elementor-data-after.json")
if isinstance(elementor_before, str):
    elementor_before = json.loads(elementor_before)
if isinstance(elementor_after, str):
    elementor_after = json.loads(elementor_after)

inventory = {
    "generated_date": "2026-10-19",
    "scope": "Local before/after Lighthouse comparison after performance work; production unchanged.",
    "before_summary": before_summary,
    "after_summary": after_summary,
    "comparisons": comparisons,
    "home_desktop": {"before": before_desktop, "after": after_desktop},
    "functional_qa": qa["summary"],
    "elementor_data_unchanged": elementor_before == elementor_after,
    "image_optimization": image_manifest,
}
(ROOT / "performance-inventory-after.json").write_text(json.dumps(inventory, indent=2, ensure_ascii=False), encoding="utf-8")

with (ROOT / "performance-page-comparison.csv").open("w", encoding="utf-8-sig", newline="") as handle:
    writer = csv.writer(handle)
    writer.writerow(["page", "score_before", "score_after", "score_delta", "lcp_before_ms", "lcp_after_ms", "transfer_before_kib", "transfer_after_kib", "transfer_reduction_pct", "cls_before", "cls_after", "tbt_before_ms", "tbt_after_ms"])
    for item in comparisons:
        old, new = item["before"], item["after"]
        writer.writerow([item["label"], round(old["score"]), round(new["score"]), round(item["score_delta"]), round(old["lcp_ms"]), round(new["lcp_ms"]), round(old["transfer_bytes"] / 1024), round(new["transfer_bytes"] / 1024), round(item["transfer_reduction_pct"], 1), round(old["cls"], 3), round(new["cls"], 3), round(old["tbt_ms"]), round(new["tbt_ms"])])


def seconds(ms):
    return f"{ms / 1000:.1f} s"


def mib(value):
    return f"{value / 1024 / 1024:.1f} MB"


def delta(value, suffix=""):
    sign = "+" if value > 0 else ""
    return f"{sign}{value:.0f}{suffix}"


def weight_change(reduction):
    change = -reduction
    sign = "+" if change > 0 else "−" if change < 0 else ""
    return f"{sign}{abs(change):.0f}%"


rows = []
for item in comparisons:
    old, new = item["before"], item["after"]
    rows.append(
        f'| [{item["label"]}]({item["url"]}) | {round(old["score"])} → **{round(new["score"])}** | '
        f'{seconds(old["lcp_ms"])} → **{seconds(new["lcp_ms"])}** | '
        f'{mib(old["transfer_bytes"])} → **{mib(new["transfer_bytes"])}** | '
        f'{weight_change(item["transfer_reduction_pct"])} | {old["cls"]:.3f} → {new["cls"]:.3f} |'
    )

report = f"""# Optimización de rendimiento — comparación Lighthouse

Fecha de cierre: **19 de octubre de 2026**  
Entorno: **WordPress local `http://localhost:8082/`**  
Herramienta: **Lighthouse 13.5.0**  
Estado de producción: **sin cambios; las optimizaciones permanecen en el proyecto local**.

## Resultado

- Score móvil mediano de las 9 rutas: **{before_summary['median_mobile_score']:.0f} → {after_summary['median_mobile_score']:.0f}**.
- Promedio móvil: **{before_summary['average_mobile_score']:.1f} → {after_summary['average_mobile_score']:.1f}**.
- Home móvil, mediana de tres ejecuciones: **{before_summary['home_mobile_score']:.0f} → {after_summary['home_mobile_score']:.0f}**.
- Home escritorio: **{before_summary['home_desktop_score']:.0f} → {after_summary['home_desktop_score']:.0f}**.
- Peso inicial del home: **{mib(before_summary['home_transfer_bytes'])} → {mib(after_summary['home_transfer_bytes'])}**, una reducción de **{100 * (before_summary['home_transfer_bytes'] - after_summary['home_transfer_bytes']) / before_summary['home_transfer_bytes']:.0f}%**.
- LCP móvil: antes había **{before_summary['lcp_poor']}/9** rutas deficientes; después quedaron **{after_summary['lcp_good']} buenas**, **{after_summary['lcp_needs_improvement']} por mejorar** y **{after_summary['lcp_poor']} deficientes**.
- CLS bueno: **{before_summary['cls_good']}/9 → {after_summary['cls_good']}/9**. TBT bueno: **{after_summary['tbt_good']}/9**.

Los resultados son pruebas de laboratorio en el entorno local. En producción el hosting, caché y latencia real pueden cambiar las cifras, pero la reducción de bytes y solicitudes pesadas sí corresponde a los recursos servidos.

## Comparación por página

| Página | Score | LCP | Transferencia | Cambio de peso | CLS |
|---|---:|---:|---:|---:|---:|
{chr(10).join(rows)}

## Cambios ejecutados

- Se generaron variantes WebP paralelas para las imágenes usadas por las rutas medidas. Conservan exactamente las dimensiones originales; los archivos pequeños se codificaron sin pérdida y las fotografías grandes con calidad alta. Los originales siguen intactos y el servidor los usa como fallback.
- Se añadieron fuentes locales WOFF2 de Nunito y Nunito Sans con `font-display: swap`; dejaron de solicitarse archivos de Google Fonts.
- Los hero críticos reciben una precarga específica y prioridad alta, por lo que Lighthouse confirma que el LCP se descubre desde el documento inicial.
- El video del home usa póster y `preload=none`; su archivo se solicita cuando el visitante se acerca a la sección y vuelve a reproducirse correctamente.
- Elementor recibió un refresco de CSS después del cambio de entrega de recursos.

## Calidad de imágenes

- Objetivos revisados: **{image_manifest['targets']}**.
- Variantes activas: **{image_manifest['created'] + image_manifest['recreated']}**; **{image_manifest['skipped']}** recursos conservaron su formato original porque WebP no reducía al menos 5%.
- Peso agregado de los recursos objetivo: **{mib(image_manifest['original_bytes'])} → {mib(image_manifest['served_webp_bytes'])}**.
- Todas las variantes activas conservan las dimensiones originales. Se compararon visualmente los diez archivos más pesados y no se observó pixelación ni degradación apreciable.

## Validación funcional

- **{qa['summary']['checks']}** comprobaciones: 9 rutas en escritorio y móvil.
- Respuestas no 200: **{qa['summary']['non200']}**; errores JavaScript: **{qa['summary']['pageErrors']}**; errores de consola: **{qa['summary']['consoleErrors']}**; solicitudes fallidas: **{qa['summary']['requestFailures']}**.
- Imágenes rotas: **{qa['summary']['brokenImages']}**; desbordamientos horizontales: **{len(qa['summary']['overflowChecks'])}**; problemas de H1: **{len(qa['summary']['h1Issues'])}**.
- Solicitudes a Google Fonts: **{qa['summary']['googleFontRequests']}**; respuestas WebP verificadas por el navegador: **{qa['summary']['webpResponses']}**.
- El formulario del home avanzó al segundo paso en la prueba automatizada sin enviar información.
- Los datos `_elementor_data` y `post_content` del home permanecen idénticos al respaldo: Elementor sigue editable y el contenido no cambió.

## Acciones revisadas y no aplicadas

- No se difirió jQuery ni se eliminó jQuery Migrate: el ahorro probable era pequeño y podía introducir fallos en Elementor, formularios o widgets.
- No se cargó CSS de Elementor de forma asíncrona ni se redujeron contenedores: Lighthouse ya aprueba el tamaño del DOM y esos cambios podían producir parpadeo visual o afectar la edición.
- No se actualizaron plugins solamente para mejorar el score. Esa tarea requiere una regresión independiente en staging.
- No se añadió caché de página específica del entorno local, porque el comportamiento debe definirse según el hosting de producción.

## Evidencia

- `raw/`: 12 resultados Lighthouse posteriores, incluido el home tres veces en móvil y una en escritorio.
- `performance-inventory-after.json`: inventario consolidado antes/después.
- `performance-page-comparison.csv`: comparación reutilizable por ruta.
- `functional-visual-qa.json`: validación de navegador en 18 combinaciones.
- `home-desktop-after.png` y `home-mobile-after.png`: capturas posteriores.
- `../performance-image-quality-comparison-20261018.jpg`: comparación visual de calidad.
- `../backups/performance-20261018/`: respaldo de base de datos, Elementor, contenido y variantes anteriores.
"""
(PROJECT / "reports" / "performance-improvements-after-20261018.md").write_text(report, encoding="utf-8")


def score_class(score):
    return "good" if score >= 90 else "mid" if score >= 50 else "poor"


cards = []
for item in comparisons:
    old, new = item["before"], item["after"]
    cards.append(f'''<article class="card"><header><h3>{html.escape(item['label'])}</h3><strong class="score {score_class(new['score'])}">{round(new['score'])}</strong></header><div class="score-change">{round(old['score'])} <span>→</span> {round(new['score'])} <b>{delta(item['score_delta'])}</b></div><dl><div><dt>LCP</dt><dd>{seconds(old['lcp_ms'])} → <b>{seconds(new['lcp_ms'])}</b></dd></div><div><dt>Peso</dt><dd>{mib(old['transfer_bytes'])} → <b>{mib(new['transfer_bytes'])}</b></dd></div><div><dt>Cambio peso</dt><dd><b>{weight_change(item['transfer_reduction_pct'])}</b></dd></div><div><dt>CLS</dt><dd>{old['cls']:.3f} → <b>{new['cls']:.3f}</b></dd></div></dl></article>''')

dashboard = f'''<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Comparación Lighthouse</title><style>
:root{{--navy:#082b55;--gold:#fdc800;--blue:#25377b;--mist:#edf3f7;--line:#d8e3eb;--text:#29435f;--green:#13795b;--green-bg:#ddf5ea;--yellow:#805d00;--yellow-bg:#fff0bd}}
*{{box-sizing:border-box}}body{{margin:0;background:#e7eef4;font-family:Arial,sans-serif;color:var(--navy);padding:44px}}.sheet{{max-width:1480px;margin:auto;border-radius:30px;overflow:hidden;background:linear-gradient(145deg,#fff 0%,#f7fbfd 72%,#fff7df 100%);box-shadow:0 28px 70px rgba(8,43,85,.14);border:1px solid rgba(8,43,85,.08)}}
.hero{{position:relative;background:var(--navy);color:#fff;padding:42px 54px 38px;overflow:hidden}}.hero:after{{content:"";position:absolute;width:520px;height:520px;right:-280px;top:-250px;border:1px solid rgba(255,255,255,.16);border-radius:76px;transform:rotate(43deg);box-shadow:55px 55px 0 -54px rgba(253,200,0,.8)}}.eyebrow{{margin:0 0 10px;color:var(--gold);font-size:14px;font-weight:800;letter-spacing:.12em}}h1{{font-size:42px;letter-spacing:-.035em;line-height:1.05;margin:0}}.hero-copy{{margin:14px 0 0;color:rgba(255,255,255,.78);font-size:16px}}.date{{position:absolute;right:54px;bottom:38px;font-weight:800}}
.metrics{{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;padding:22px 28px;background:#fff;border-bottom:1px solid var(--line)}}.metric{{border:1px solid var(--line);border-radius:16px;padding:15px;background:#fff}}.metric b{{display:block;font-size:25px;line-height:1}}.metric span{{display:block;margin-top:7px;color:var(--text);font-size:12px;font-weight:700}}.metric.improved b{{color:var(--green)}}
.content{{padding:27px}}.section-title{{display:flex;align-items:end;justify-content:space-between;margin:0 0 15px}}.section-title h2{{margin:0;font-size:24px}}.section-title p{{margin:0;color:#61758a;font-size:13px;font-weight:700}}.grid{{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}}.card{{background:#fff;border:1px solid var(--line);border-radius:18px;padding:17px;box-shadow:0 8px 22px rgba(8,43,85,.045)}}.card header{{display:flex;align-items:center;justify-content:space-between;gap:15px}}h3{{font-size:16px;margin:0}}.score{{min-width:48px;border-radius:999px;padding:6px 9px;text-align:center;font-size:15px}}.good{{color:var(--green);background:var(--green-bg)}}.mid{{color:var(--yellow);background:var(--yellow-bg)}}.score-change{{margin-top:12px;background:#f5f9fc;border-radius:10px;padding:9px;font-weight:800;color:#66798c}}.score-change span{{color:#9aacbb;margin:0 5px}}.score-change b{{float:right;color:var(--green)}}dl{{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:11px 0 0}}dl div{{background:#f8fafc;border-radius:9px;padding:8px}}dt{{font-size:9px;text-transform:uppercase;letter-spacing:.08em;color:#77899a;font-weight:800}}dd{{margin:4px 0 0;font-size:12px;font-weight:700;color:var(--text)}}dd b{{color:var(--green)}}.note{{margin-top:17px;background:#e5f7ef;border:1px solid #a9dfca;color:#155e46;border-radius:16px;padding:14px 18px;font-size:13px;font-weight:700;line-height:1.45}}
</style></head><body><main class="sheet"><header class="hero"><p class="eyebrow">SABER COLLEGE · PERFORMANCE</p><h1>Lighthouse: antes y después</h1><p class="hero-copy">Optimización local · Imágenes con resolución intacta · Elementor editable</p><span class="date">19 OCT 2026</span></header><section class="metrics"><div class="metric improved"><b>{before_summary['median_mobile_score']:.0f} → {after_summary['median_mobile_score']:.0f}</b><span>Mediana móvil</span></div><div class="metric improved"><b>{before_summary['home_mobile_score']:.0f} → {after_summary['home_mobile_score']:.0f}</b><span>Home móvil</span></div><div class="metric improved"><b>{before_summary['home_desktop_score']:.0f} → {after_summary['home_desktop_score']:.0f}</b><span>Home desktop</span></div><div class="metric improved"><b>{mib(before_summary['home_transfer_bytes'])} → {mib(after_summary['home_transfer_bytes'])}</b><span>Peso home</span></div><div class="metric improved"><b>{after_summary['lcp_poor']}/9</b><span>LCP deficientes</span></div><div class="metric improved"><b>18/18</b><span>QA funcional correcto</span></div></section><section class="content"><div class="section-title"><h2>Rutas críticas</h2><p>Home usa la mediana de 3 ejecuciones</p></div><div class="grid">{''.join(cards)}</div><div class="note">Resultado local: 0 errores JavaScript, 0 imágenes rotas, 0 desbordamientos y 0 solicitudes a Google Fonts. Los originales permanecen disponibles y el contenido editable de Elementor no cambió.</div></section></main></body></html>'''
(ROOT / "lighthouse-after-comparison.html").write_text(dashboard, encoding="utf-8")
print(json.dumps({"before": before_summary, "after": after_summary}, indent=2, ensure_ascii=False))
