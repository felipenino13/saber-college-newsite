import html
import csv
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent
RAW = json.loads((ROOT / "rank-math-raw-inventory.json").read_text(encoding="utf-8-sig"))
RENDERED = json.loads((ROOT / "rendered-page-inventory.json").read_text(encoding="utf-8-sig"))
RENDERED_BY_ID = {item["id"]: item for item in RENDERED["pages"]}

GROUPS = {
    "priority": {
        "label": "Grupo 1 · Páginas prioritarias",
        "description": "Captación, programas, admisiones, navegación principal y búsquedas con intención alta.",
        "ids": [7, 30, 34, 36, 861, 870, 872, 877, 881, 883, 898, 911, 1192, 1225, 1238, 1591],
    },
    "institutional": {
        "label": "Grupo 2 · Páginas institucionales",
        "description": "Confianza, cumplimiento, servicios, documentos y soporte al estudiante.",
        "ids": [25, 859, 863, 885, 887, 896, 900, 902, 904, 913, 915, 917, 919, 921, 923, 925, 1229, 1232, 1628, 1633],
    },
    "noindex": {
        "label": "Grupo 3 · Mantener fuera del índice",
        "description": "Páginas internas, de confirmación o landings pagadas que actualmente usan noindex.",
        "ids": [1328, 1621, 1638, 1639, 1640, 1641, 2066],
    },
}

all_ids = {page["id"] for page in RAW["pages"]}
grouped_ids = [item for group in GROUPS.values() for item in group["ids"]]
assert len(grouped_ids) == len(set(grouped_ids)), "A page appears in more than one group"
assert set(grouped_ids) == all_ids, f"Unclassified IDs: {sorted(all_ids - set(grouped_ids))}"

pages = []
for source in RAW["pages"]:
    page = dict(source)
    page.update({f"rendered_{k}": v for k, v in RENDERED_BY_ID[page["id"]].items() if k not in {"id", "url"}})
    page["score"] = int(page["seo_score"] or 0)
    page["is_noindex"] = "noindex" in page["robots"]
    page["score_band"] = "green" if page["score"] >= 81 else "yellow" if page["score"] >= 51 else "red"
    page["title_length"] = len(page.get("rendered_document_title", ""))
    page["description_length"] = len(page.get("rendered_meta_description", ""))
    page["issues"] = []
    if not page["focus_keyword"]:
        page["issues"].append("Sin palabra clave")
    if not page["is_noindex"] and page["score"] <= 50:
        page["issues"].append("Puntuación roja")
    if page["title_length"] < 30 or page["title_length"] > 60:
        page["issues"].append(f"Título {page['title_length']} caracteres")
    if page["description_length"] < 120 or page["description_length"] > 160:
        page["issues"].append(f"Descripción {page['description_length']} caracteres")
    if page.get("rendered_missing_alt_count", 0):
        page["issues"].append(f"{page['rendered_missing_alt_count']} imágenes sin alt")
    for key, group in GROUPS.items():
        if page["id"] in group["ids"]:
            page["group"] = key
            break
    pages.append(page)

indexable = [page for page in pages if not page["is_noindex"]]
summary = {
    "generated_date": "2026-10-18",
    "scope": "Local WordPress only; read-only audit; no SEO settings changed.",
    "total_pages": len(pages),
    "indexable_pages": len(indexable),
    "noindex_pages": sum(page["is_noindex"] for page in pages),
    "average_indexable_score": round(sum(page["score"] for page in indexable) / len(indexable), 1),
    "red_indexable": sum(page["score_band"] == "red" for page in indexable),
    "yellow_indexable": sum(page["score_band"] == "yellow" for page in indexable),
    "green_indexable": sum(page["score_band"] == "green" for page in indexable),
    "pages_with_focus_keyword": sum(bool(page["focus_keyword"]) for page in pages),
    "pages_with_specialized_schema": sum(bool(page["schema_type"]) for page in pages),
    "pages_with_base_schema_rendered": sum(page.get("rendered_schema_blocks", 0) > 0 for page in pages),
    "pages_http_200": sum(page.get("rendered_http_status") == 200 for page in pages),
    "pages_with_exactly_one_h1": sum(page.get("rendered_h1_count") == 1 for page in pages),
    "pages_with_browser_errors": sum(bool(page.get("rendered_page_errors")) for page in pages),
    "missing_alt_total": sum(page.get("rendered_missing_alt_count", 0) for page in pages),
    "pages_with_missing_alt": sum(page.get("rendered_missing_alt_count", 0) > 0 for page in pages),
    "groups": {key: len(group["ids"]) for key, group in GROUPS.items()},
}

output = {
    "summary": summary,
    "groups": GROUPS,
    "pages": pages,
}
(ROOT / "rank-math-inventory-grouped-before.json").write_text(json.dumps(output, indent=2, ensure_ascii=False), encoding="utf-8")

with (ROOT / "rank-math-inventory-grouped-before.csv").open("w", encoding="utf-8-sig", newline="") as handle:
    writer = csv.writer(handle)
    writer.writerow([
        "group", "id", "title", "url", "indexing", "rank_math_score", "score_band",
        "focus_keyword", "rendered_title", "title_length", "rendered_description",
        "description_length", "canonical", "h1_count", "schema_types",
        "internal_links", "external_links", "images_missing_alt", "issues"
    ])
    for page in pages:
        writer.writerow([
            page["group"], page["id"], page["title"], page["url"],
            "noindex" if page["is_noindex"] else "index",
            page["score"], page["score_band"], page["focus_keyword"],
            page.get("rendered_document_title", ""), page["title_length"],
            page.get("rendered_meta_description", ""), page["description_length"],
            page.get("rendered_canonical", ""), page.get("rendered_h1_count", 0),
            ", ".join(page.get("rendered_schema_types", [])),
            page.get("rendered_unique_internal_links", 0), page.get("rendered_unique_external_links", 0),
            page.get("rendered_missing_alt_count", 0), "; ".join(page["issues"])
        ])

def badge(page):
    if page["is_noindex"]:
        return "Noindex"
    return f"{page['score']}/100"

def md_table(group_key):
    selected = [page for page in pages if page["group"] == group_key]
    rows = ["| ID | Página | Rank Math | Keyword | Estado técnico actual |", "|---:|---|---:|---|---|"]
    for page in selected:
        issues = "; ".join(page["issues"]) if page["issues"] else "Sin alertas básicas"
        rows.append(
            f"| {page['id']} | [{page['title']}]({page['url']}) | {badge(page)} | {page['focus_keyword'] or '—'} | {issues} |"
        )
    return "\n".join(rows)

md = f"""# Inventario SEO de Rank Math — estado inicial

Fecha del corte: **18 de octubre de 2026**  
Entorno: **WordPress local `http://localhost:8082/`**  
Alcance: auditoría de solo lectura. **No se modificaron páginas, metadatos, robots, schema ni contenido.**

## Resumen ejecutivo

- Páginas publicadas inventariadas: **{summary['total_pages']}**.
- Páginas indexables: **{summary['indexable_pages']}**.
- Páginas con `noindex`: **{summary['noindex_pages']}**.
- Promedio Rank Math de las páginas indexables: **{summary['average_indexable_score']}/100**.
- Distribución indexable: **{summary['red_indexable']} rojas**, **{summary['yellow_indexable']} amarillas** y **{summary['green_indexable']} verde**.
- Palabra clave configurada: **{summary['pages_with_focus_keyword']} de {summary['total_pages']}** páginas.
- Schema específico configurado por página: **0**. Rank Math sí genera schema base en el HTML de las **{summary['pages_with_base_schema_rendered']} páginas**.
- Respuesta HTTP 200: **{summary['pages_http_200']} de {summary['total_pages']}**.
- Una sola etiqueta H1: **{summary['pages_with_exactly_one_h1']} de {summary['total_pages']}**.
- Errores de navegador durante el rastreo: **{summary['pages_with_browser_errors']}**.
- Imágenes sin texto alternativo: **{summary['missing_alt_total']}** distribuidas en **{summary['pages_with_missing_alt']} páginas**.

La puntuación baja de Rank Math es el principal problema visible, pero el rastreo también confirma una base técnica estable: todas las páginas responden, todas tienen un H1 y Rank Math genera schema base. El siguiente trabajo debe priorizar metadatos, intención de búsqueda, enlaces, atributos alternativos y schema especializado cuando el contenido lo justifique.

## Criterio de agrupación

1. **Páginas prioritarias:** captación, programas, admisiones, hubs principales y búsquedas de alta intención.
2. **Páginas institucionales:** confianza, servicios, cumplimiento, documentación y soporte.
3. **Mantener fuera del índice:** confirmaciones, herramientas internas y landings de campañas que ya tienen `noindex`. No se propone cambiar su indexación durante la optimización general.

## Grupo 1 · Páginas prioritarias

{md_table('priority')}

## Grupo 2 · Páginas institucionales

{md_table('institutional')}

## Grupo 3 · Mantener fuera del índice

{md_table('noindex')}

## Lectura del estado actual

- **Rojo:** puntuación de 0 a 50 en una página indexable.
- **Amarillo:** puntuación de 51 a 80.
- **Verde:** puntuación de 81 a 100.
- **Noindex:** se registra por separado; no se considera un error de puntuación cuando responde al propósito de la página.
- Los avisos de longitud usan como referencia operativa 30–60 caracteres para el título y 120–160 para la descripción. Se revisarán por legibilidad e intención, no solo por longitud.
- “Schema específico: 0” significa que ninguna página tiene un tipo individual guardado en su metadato de Rank Math. El HTML actual sí contiene `WebPage`, `WebSite`, `Organization` y `BreadcrumbList` generados por el plugin cuando corresponde.

## Evidencia conservada para la comparación posterior

- `rank-math-raw-inventory.json`: valores originales guardados por Rank Math.
- `rendered-page-inventory.json`: HTML servido, estado HTTP, metadatos, encabezados, schema, enlaces e imágenes.
- `rank-math-inventory-grouped-before.json`: inventario consolidado con los tres grupos.
- `rank-math-before-overview.html` y `rank-math-before-overview.png`: fotografía visual del estado inicial.

Este corte se utilizará como línea base. Después de aplicar cambios se repetirá el mismo proceso y se compararán puntuación, metadatos, indexación, schema, enlaces, encabezados, imágenes y errores de navegador.
"""
(ROOT.parent / "rank-math-inventory-before-20261018.md").write_text(md, encoding="utf-8")

def item_html(page):
    state = "noindex" if page["is_noindex"] else page["score_band"]
    value = "NOINDEX" if page["is_noindex"] else str(page["score"])
    return f'''<li><span class="page-name">{html.escape(page['title'])}</span><span class="score {state}">{value}</span></li>'''

def group_html(key):
    group = GROUPS[key]
    selected = [page for page in pages if page["group"] == key]
    return f'''<section class="group {key}">
      <header><div><p>{html.escape(group['label'].split(' · ')[0])}</p><h2>{html.escape(group['label'].split(' · ')[1])}</h2></div><strong>{len(selected)} páginas</strong></header>
      <ul>{''.join(item_html(page) for page in selected)}</ul>
    </section>'''

dashboard = f'''<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Rank Math baseline</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');
:root{{--navy:#082b55;--blue:#25377b;--gold:#fdc800;--paper:#fff;--mist:#f3f7fa;--body:#29435f;--line:#dce5ec;--red:#b42318;--red-bg:#fee4e2;--yellow:#775800;--yellow-bg:#fff2c6;--green:#176447;--green-bg:#dff7eb}}
*{{box-sizing:border-box}} body{{margin:0;background:#eaf0f5;color:var(--navy);font-family:Nunito,Arial,sans-serif;padding:54px}} .sheet{{max-width:1492px;margin:auto;background:linear-gradient(145deg,#fff 0%,#f8fbfe 74%,#fff8e4 100%);border-radius:30px;box-shadow:0 28px 72px rgba(8,43,85,.13);overflow:hidden;border:1px solid rgba(8,43,85,.09)}}
.hero{{position:relative;background:var(--navy);color:white;padding:50px 58px 44px;overflow:hidden}} .hero:after{{content:"";position:absolute;width:540px;height:540px;border:1px solid rgba(255,255,255,.16);border-radius:76px;right:-285px;top:-240px;transform:rotate(43deg);box-shadow:55px 55px 0 -54px rgba(253,200,0,.75)}} .eyebrow{{margin:0 0 11px;color:var(--gold);font-weight:800;letter-spacing:.12em;font-size:14px}} h1{{margin:0;font-size:46px;line-height:1.05;letter-spacing:-.035em}} .hero-copy{{margin:15px 0 0;color:rgba(255,255,255,.78);font-size:17px}} .date{{position:absolute;right:58px;bottom:44px;color:white;font-weight:700}}
.metrics{{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;padding:24px 30px;background:white;border-bottom:1px solid var(--line)}} .metric{{padding:17px 18px;border:1px solid var(--line);border-radius:16px;background:#fff}} .metric b{{display:block;font-size:27px;line-height:1}} .metric span{{display:block;margin-top:7px;color:var(--body);font-size:13px;font-weight:700}} .metric.red b{{color:var(--red)}} .metric.yellow b{{color:var(--yellow)}} .metric.green b{{color:var(--green)}}
.content{{padding:30px;display:grid;grid-template-columns:1fr 1fr;gap:22px;align-items:start}} .group{{background:white;border:1px solid var(--line);border-radius:20px;padding:22px;box-shadow:0 9px 28px rgba(8,43,85,.05)}} .group header{{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;padding-bottom:15px;border-bottom:1px solid var(--line)}} .group header p{{margin:0 0 3px;color:var(--blue);font-size:12px;font-weight:800;letter-spacing:.11em;text-transform:uppercase}} .group h2{{margin:0;font-size:23px}} .group header strong{{color:var(--body);font-size:13px;white-space:nowrap;background:var(--mist);border-radius:999px;padding:7px 10px}} ul{{list-style:none;margin:12px 0 0;padding:0;display:grid;gap:3px}} li{{display:flex;align-items:center;justify-content:space-between;gap:14px;min-height:31px;padding:4px 2px;border-bottom:1px solid #edf2f6}} li:last-child{{border:0}} .page-name{{font-size:13.5px;font-weight:700;color:var(--body)}} .score{{min-width:50px;text-align:center;border-radius:999px;padding:4px 7px;font-size:12px;font-weight:800}} .score.red{{color:var(--red);background:var(--red-bg)}} .score.yellow{{color:var(--yellow);background:var(--yellow-bg)}} .score.green{{color:var(--green);background:var(--green-bg)}} .score.noindex{{color:#52677b;background:#edf1f4;min-width:70px}} .noindex{{grid-column:1/-1}} .noindex ul{{grid-template-columns:repeat(2,1fr);column-gap:28px}} .footer-note{{grid-column:1/-1;background:#fff8e4;border:1px solid #f4df94;border-radius:18px;padding:17px 20px;color:#5d4a00;font-size:14px;font-weight:700;line-height:1.45}}
</style></head><body><main class="sheet">
<header class="hero"><p class="eyebrow">SABER COLLEGE · SEO BASELINE</p><h1>Rank Math: estado antes de optimizar</h1><p class="hero-copy">Inventario local de solo lectura · 43 páginas publicadas · Sin cambios aplicados</p><span class="date">18 OCT 2026</span></header>
<section class="metrics">
<div class="metric"><b>{summary['total_pages']}</b><span>Páginas</span></div><div class="metric"><b>{summary['indexable_pages']}</b><span>Indexables</span></div><div class="metric red"><b>{summary['red_indexable']}</b><span>En rojo</span></div><div class="metric yellow"><b>{summary['yellow_indexable']}</b><span>En amarillo</span></div><div class="metric green"><b>{summary['green_indexable']}</b><span>En verde</span></div><div class="metric"><b>{summary['average_indexable_score']}</b><span>Promedio / 100</span></div>
</section>
<div class="content">{group_html('priority')}{group_html('institutional')}{group_html('noindex')}<div class="footer-note">Base técnica observada: 43/43 páginas responden HTTP 200, 43/43 tienen un solo H1 y no se registraron errores de navegador. Hay 26 imágenes sin texto alternativo en 11 páginas. Las 7 páginas noindex permanecen separadas de la puntuación indexable.</div></div>
</main></body></html>'''
(ROOT / "rank-math-before-overview.html").write_text(dashboard, encoding="utf-8")
print(json.dumps(summary, indent=2, ensure_ascii=False))
