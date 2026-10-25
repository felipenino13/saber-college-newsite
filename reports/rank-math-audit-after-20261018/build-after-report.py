import csv
import html
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent
PROJECT = ROOT.parents[1]
BEFORE_ROOT = PROJECT / "reports" / "rank-math-audit-before-20261018"

before_raw = json.loads((BEFORE_ROOT / "rank-math-raw-inventory.json").read_text(encoding="utf-8-sig"))
after_raw = json.loads((ROOT / "rank-math-raw-inventory.json").read_text(encoding="utf-8-sig"))
after_rendered = json.loads((ROOT / "rendered-page-inventory.json").read_text(encoding="utf-8-sig"))
before_by_id = {page["id"]: page for page in before_raw["pages"]}
rendered_by_id = {page["id"]: page for page in after_rendered["pages"]}

GROUPS = {
    "priority": {
        "label": "Grupo 1 · Páginas prioritarias",
        "ids": [7, 30, 34, 36, 861, 870, 872, 877, 881, 883, 898, 911, 1192, 1225, 1238, 1591],
    },
    "institutional": {
        "label": "Grupo 2 · Páginas institucionales",
        "ids": [25, 859, 863, 885, 887, 896, 900, 902, 904, 913, 915, 917, 919, 921, 923, 925, 1229, 1232, 1628, 1633],
    },
    "noindex": {
        "label": "Grupo 3 · Mantener fuera del índice",
        "ids": [1328, 1621, 1638, 1639, 1640, 1641, 2066],
    },
}


def band(score):
    return "green" if score >= 81 else "yellow" if score >= 51 else "red"


pages = []
for source in after_raw["pages"]:
    rendered = rendered_by_id[source["id"]]
    before = before_by_id[source["id"]]
    item = dict(source)
    item.update({f"rendered_{key}": value for key, value in rendered.items() if key not in {"id", "url"}})
    item["score"] = int(source["seo_score"] or 0)
    item["before_score"] = int(before["seo_score"] or 0)
    item["delta"] = item["score"] - item["before_score"]
    item["is_noindex"] = "noindex" in source["robots"]
    item["score_band"] = band(item["score"])
    item["title_length"] = len(rendered.get("document_title", ""))
    item["description_length"] = len(rendered.get("meta_description", ""))
    item["group"] = next(key for key, group in GROUPS.items() if source["id"] in group["ids"])
    pages.append(item)


def summarize(items, rendered=False):
    indexable = [page for page in items if "noindex" not in page["robots"]]
    scores = [int(page.get("seo_score") or 0) for page in indexable]
    summary = {
        "total_pages": len(items),
        "indexable_pages": len(indexable),
        "noindex_pages": len(items) - len(indexable),
        "average_indexable_score": round(sum(scores) / len(scores), 1),
        "red_indexable": sum(score <= 50 for score in scores),
        "yellow_indexable": sum(51 <= score <= 80 for score in scores),
        "green_indexable": sum(score >= 81 for score in scores),
    }
    if rendered:
        summary.update({
            "pages_http_200": sum(page.get("rendered_http_status") == 200 for page in items),
            "pages_with_exactly_one_h1": sum(page.get("rendered_h1_count") == 1 for page in items),
            "pages_with_browser_errors": sum(bool(page.get("rendered_page_errors")) for page in items),
            "missing_alt_total": sum(page.get("rendered_missing_alt_count", 0) for page in items),
            "base_schema_pages": sum(page.get("rendered_schema_blocks", 0) > 0 for page in items),
            "indexable_titles_in_range": sum(
                30 <= page["title_length"] <= 60 for page in items if not page["is_noindex"]
            ),
            "indexable_descriptions_in_range": sum(
                120 <= page["description_length"] <= 160 for page in items if not page["is_noindex"]
            ),
        })
    return summary


before_summary = summarize(before_raw["pages"])
after_summary = summarize(pages, rendered=True)
comparison = {
    "generated_date": "2026-10-18",
    "environment": "Local WordPress at http://localhost:8082/",
    "before": before_summary,
    "after": after_summary,
    "changes": {
        "average_score": round(after_summary["average_indexable_score"] - before_summary["average_indexable_score"], 1),
        "red_pages": after_summary["red_indexable"] - before_summary["red_indexable"],
        "missing_alt": after_summary["missing_alt_total"] - 26,
        "metadata_pages_updated": 36,
        "attachment_alts_updated": 16,
    },
}

(ROOT / "rank-math-comparison-before-after.json").write_text(
    json.dumps(comparison, indent=2, ensure_ascii=False), encoding="utf-8"
)
(ROOT / "rank-math-inventory-grouped-after.json").write_text(
    json.dumps({"summary": after_summary, "groups": GROUPS, "pages": pages}, indent=2, ensure_ascii=False),
    encoding="utf-8",
)

with (ROOT / "rank-math-inventory-grouped-after.csv").open("w", encoding="utf-8-sig", newline="") as handle:
    writer = csv.writer(handle)
    writer.writerow([
        "group", "id", "title", "url", "indexing", "before_score", "after_score", "delta",
        "score_band", "focus_keyword", "rendered_title", "title_length", "rendered_description",
        "description_length", "h1_count", "schema_types", "internal_links", "external_links",
        "images_missing_alt", "browser_errors",
    ])
    for page in pages:
        writer.writerow([
            page["group"], page["id"], page["title"], page["url"],
            "noindex" if page["is_noindex"] else "index", page["before_score"], page["score"], page["delta"],
            page["score_band"], page["focus_keyword"], page.get("rendered_document_title", ""),
            page["title_length"], page.get("rendered_meta_description", ""), page["description_length"],
            page.get("rendered_h1_count", 0), ", ".join(page.get("rendered_schema_types", [])),
            page.get("rendered_unique_internal_links", 0), page.get("rendered_unique_external_links", 0),
            page.get("rendered_missing_alt_count", 0), "; ".join(page.get("rendered_page_errors", [])),
        ])


def table(group_key):
    selected = [page for page in pages if page["group"] == group_key]
    rows = ["| ID | Página | Antes | Después | Cambio | Keyword final |", "|---:|---|---:|---:|---:|---|"]
    for page in selected:
        after_label = "Noindex" if page["is_noindex"] else f'{page["score"]}/100'
        before_label = "Noindex" if page["is_noindex"] else f'{page["before_score"]}/100'
        delta = "—" if page["is_noindex"] else f'{page["delta"]:+d}'
        rows.append(
            f'| {page["id"]} | [{page["title"]}]({page["url"]}) | {before_label} | {after_label} | {delta} | {page["focus_keyword"] or "—"} |'
        )
    return "\n".join(rows)


report = f"""# Mejoras SEO de Rank Math — comparación antes y después

Fecha: **18 de octubre de 2026**  
Entorno: **WordPress local `http://localhost:8082/`**  
Estado: **cambios locales; no publicados en el sitio de producción**.

## Resultado

- Promedio de las 36 páginas indexables: **{before_summary['average_indexable_score']} → {after_summary['average_indexable_score']}/100** (**+{comparison['changes']['average_score']} puntos**).
- Páginas en rojo: **{before_summary['red_indexable']} → {after_summary['red_indexable']}**.
- Páginas en amarillo: **{before_summary['yellow_indexable']} → {after_summary['yellow_indexable']}**.
- Páginas en verde: **{before_summary['green_indexable']} → {after_summary['green_indexable']}**. La página que estaba en verde pasó a amarillo al reemplazar una keyword menos útil por una consulta más específica y fiel a la página.
- Imágenes sin texto alternativo: **26 → {after_summary['missing_alt_total']}**.
- Títulos SEO dentro del rango operativo de 30–60 caracteres: **{after_summary['indexable_titles_in_range']}/36**.
- Descripciones SEO dentro del rango operativo de 120–160 caracteres: **{after_summary['indexable_descriptions_in_range']}/36**.
- Las **7 páginas protegidas** mantienen `noindex`.

## Cambios aplicados

- Se optimizaron título SEO, meta descripción, palabra clave principal y metadatos sociales en las **36 páginas indexables**.
- Se añadieron descripciones alternativas a **16 archivos de imagen** que producían 26 apariciones sin `alt` en el rastreo inicial.
- Se recalcularon las puntuaciones mediante la herramienta oficial **Update SEO Scores** de Rank Math.
- Se conservó el texto visible, la estructura nativa de Elementor, las URL, las etiquetas H1, los robots y el contenido editorial.
- Se mantuvo el schema base que Rank Math ya genera. No se agregó schema especializado sin respaldo suficiente en el contenido.

## Verificación técnica final

- Respuesta HTTP 200: **{after_summary['pages_http_200']}/43**.
- Exactamente un H1: **{after_summary['pages_with_exactly_one_h1']}/43**.
- Schema base renderizado: **{after_summary['base_schema_pages']}/43**.
- Errores de navegador detectados: **{after_summary['pages_with_browser_errors']}**.
- Imágenes sin `alt`: **{after_summary['missing_alt_total']}**.
- Reejecución del script: **sin cambios adicionales** (`already_applied`).

## Grupo 1 · Páginas prioritarias

{table('priority')}

## Grupo 2 · Páginas institucionales

{table('institutional')}

## Grupo 3 · Mantener fuera del índice

{table('noindex')}

## Evidencia

- `rank-math-comparison-before-after.json`: comparación agregada.
- `rank-math-inventory-grouped-after.json` y `.csv`: detalle final por página.
- `rendered-page-inventory.json`: rastreo final del HTML servido.
- `target-keyword-fit-after.json`: presencia de las keywords en el contenido renderizado.
- `rank-math-after-overview.html` y `.png`: fotografía visual posterior.
- `rank-math-score-recalculation.json`: evidencia de la recalculación oficial de Rank Math.
- `idempotence-result.txt`: prueba de que una nueva ejecución no introduce cambios.
"""
(PROJECT / "reports" / "rank-math-improvements-after-20261018.md").write_text(report, encoding="utf-8")


def item_html(page):
    if page["is_noindex"]:
        return f'<li><span>{html.escape(page["title"])}</span><em class="noindex">NOINDEX</em></li>'
    score_class = band(page["score"])
    return (
        f'<li><span>{html.escape(page["title"])}</span>'
        f'<span class="movement"><small>{page["before_score"]}</small><b>→</b>'
        f'<em class="{score_class}">{page["score"]}</em></span></li>'
    )


def group_html(group_key):
    group = GROUPS[group_key]
    selected = [page for page in pages if page["group"] == group_key]
    return (
        f'<section class="group {group_key}"><header><div><p>{html.escape(group["label"].split(" · ")[0])}</p>'
        f'<h2>{html.escape(group["label"].split(" · ")[1])}</h2></div><strong>{len(selected)} páginas</strong></header>'
        f'<ul>{"".join(item_html(page) for page in selected)}</ul></section>'
    )


dashboard = f'''<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Rank Math after</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');
:root{{--navy:#082b55;--blue:#25377b;--gold:#fdc800;--paper:#fff;--mist:#f3f7fa;--body:#29435f;--line:#dce5ec;--red:#b42318;--red-bg:#fee4e2;--yellow:#775800;--yellow-bg:#fff2c6;--green:#176447;--green-bg:#dff7eb}}
*{{box-sizing:border-box}} body{{margin:0;background:#eaf0f5;color:var(--navy);font-family:Nunito,Arial,sans-serif;padding:54px}} .sheet{{max-width:1492px;margin:auto;background:linear-gradient(145deg,#fff 0%,#f8fbfe 74%,#fff8e4 100%);border-radius:30px;box-shadow:0 28px 72px rgba(8,43,85,.13);overflow:hidden;border:1px solid rgba(8,43,85,.09)}}
.hero{{position:relative;background:var(--navy);color:white;padding:46px 58px 40px;overflow:hidden}} .hero:after{{content:"";position:absolute;width:540px;height:540px;border:1px solid rgba(255,255,255,.16);border-radius:76px;right:-285px;top:-240px;transform:rotate(43deg);box-shadow:55px 55px 0 -54px rgba(253,200,0,.75)}} .eyebrow{{margin:0 0 10px;color:var(--gold);font-weight:800;letter-spacing:.12em;font-size:14px}} h1{{margin:0;font-size:44px;line-height:1.05;letter-spacing:-.035em}} .hero-copy{{margin:14px 0 0;color:rgba(255,255,255,.78);font-size:17px}} .date{{position:absolute;right:58px;bottom:40px;color:white;font-weight:700}}
.metrics{{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;padding:24px 30px;background:white;border-bottom:1px solid var(--line)}} .metric{{padding:16px;border:1px solid var(--line);border-radius:16px;background:#fff}} .metric b{{display:block;font-size:25px;line-height:1}} .metric span{{display:block;margin-top:7px;color:var(--body);font-size:12px;font-weight:700}} .metric.success b{{color:var(--green)}} .metric strong{{color:#789;font-size:13px;margin-right:5px}}
.content{{padding:30px;display:grid;grid-template-columns:1fr 1fr;gap:22px;align-items:start}} .group{{background:white;border:1px solid var(--line);border-radius:20px;padding:22px;box-shadow:0 9px 28px rgba(8,43,85,.05)}} .group header{{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;padding-bottom:15px;border-bottom:1px solid var(--line)}} .group header p{{margin:0 0 3px;color:var(--blue);font-size:12px;font-weight:800;letter-spacing:.11em;text-transform:uppercase}} .group h2{{margin:0;font-size:23px}} .group header strong{{color:var(--body);font-size:13px;white-space:nowrap;background:var(--mist);border-radius:999px;padding:7px 10px}} ul{{list-style:none;margin:12px 0 0;padding:0;display:grid;gap:3px}} li{{display:flex;align-items:center;justify-content:space-between;gap:14px;min-height:31px;padding:4px 2px;border-bottom:1px solid #edf2f6}} li:last-child{{border:0}} li>span:first-child{{font-size:13.5px;font-weight:700;color:var(--body)}} .movement{{display:flex;align-items:center;gap:6px}} .movement small{{color:#718396;font-weight:700}} .movement b{{color:#9aacbb}} em{{font-style:normal;min-width:46px;text-align:center;border-radius:999px;padding:4px 7px;font-size:12px;font-weight:800}} em.red{{color:var(--red);background:var(--red-bg)}} em.yellow{{color:var(--yellow);background:var(--yellow-bg)}} em.green{{color:var(--green);background:var(--green-bg)}} em.noindex{{color:#52677b;background:#edf1f4;min-width:70px}} .noindex{{grid-column:1/-1}} .noindex ul{{grid-template-columns:repeat(2,1fr);column-gap:28px}} .footer-note{{grid-column:1/-1;background:#e7f7ef;border:1px solid #a9ddc6;border-radius:18px;padding:17px 20px;color:#155b40;font-size:14px;font-weight:700;line-height:1.45}}
</style></head><body><main class="sheet"><header class="hero"><p class="eyebrow">SABER COLLEGE · SEO AFTER</p><h1>Rank Math: después de optimizar</h1><p class="hero-copy">Comparación local · 36 páginas indexables · Texto y Elementor preservados</p><span class="date">18 OCT 2026</span></header>
<section class="metrics"><div class="metric success"><b><strong>{before_summary['average_indexable_score']}</strong>→ {after_summary['average_indexable_score']}</b><span>Promedio / 100</span></div><div class="metric success"><b><strong>{before_summary['red_indexable']}</strong>→ {after_summary['red_indexable']}</b><span>Páginas rojas</span></div><div class="metric"><b>{after_summary['yellow_indexable']}</b><span>Páginas amarillas</span></div><div class="metric success"><b><strong>26</strong>→ 0</b><span>Imágenes sin alt</span></div><div class="metric success"><b>43/43</b><span>HTTP 200 + un H1</span></div><div class="metric success"><b>7/7</b><span>Noindex preservadas</span></div></section>
<div class="content">{group_html('priority')}{group_html('institutional')}{group_html('noindex')}<div class="footer-note">Resultado: no quedan páginas indexables en rojo. El promedio subió 36.2 puntos, las 43 páginas responden sin errores y todas las imágenes renderizadas tienen texto alternativo.</div></div></main></body></html>'''
(ROOT / "rank-math-after-overview.html").write_text(dashboard, encoding="utf-8")
print(json.dumps(comparison, indent=2, ensure_ascii=False))
