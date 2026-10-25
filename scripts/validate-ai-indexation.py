#!/usr/bin/env python3
"""Validate the local SABER College AI-indexation improvements."""

from __future__ import annotations

import argparse
import html
import json
import re
import urllib.request
from pathlib import Path


def fetch(url: str, user_agent: str = "SABER-College-AI-Validation/1.0") -> tuple[int, str]:
    request = urllib.request.Request(url, headers={"User-Agent": user_agent})
    with urllib.request.urlopen(request, timeout=30) as response:
        return response.status, response.read().decode(
            response.headers.get_content_charset() or "utf-8", "replace"
        )


def schema_entities(document: str) -> list[dict]:
    entities: list[dict] = []
    for _, block in re.findall(
        r"<script\b[^>]*type\s*=\s*([\"'])application/ld\+json\1[^>]*>(.*?)</script>",
        document,
        re.I | re.S,
    ):
        value = json.loads(html.unescape(block))
        if isinstance(value, dict) and isinstance(value.get("@graph"), list):
            entities.extend(item for item in value["@graph"] if isinstance(item, dict))
        elif isinstance(value, dict):
            entities.append(value)
    return entities


def has_type(entity: dict, expected: str) -> bool:
    value = entity.get("@type", [])
    return expected in ([value] if isinstance(value, str) else value)


def metrics(inventory: dict) -> dict:
    pages = inventory["pages"]
    live = [page for page in pages if page["http_status"] == 200]
    return {
        "sitemap_urls": len(pages),
        "non_200": sum(page["http_status"] != 200 for page in pages),
        "missing_descriptions": sum(not page["meta_description"] for page in live),
        "missing_canonicals": sum(not page["canonical"] for page in live),
        "pages_without_exactly_one_h1": sum(page["h1_count"] != 1 for page in live),
        "invalid_json_ld_blocks": sum(page["invalid_schema_block_count"] for page in pages),
        "missing_image_alt_attributes": sum(page["missing_alt_count"] for page in pages),
    }


def elementor_stats(path: Path) -> dict:
    data = json.loads(path.read_text(encoding="utf-8-sig"))
    nodes = 0
    widgets = 0
    visible: list[str] = []
    visible_keys = {"title", "editor", "text", "button_text", "description", "caption"}

    def walk(value):
        nonlocal nodes, widgets
        if isinstance(value, list):
            for child in value:
                walk(child)
        elif isinstance(value, dict):
            if "elType" in value:
                nodes += 1
                widgets += int(value.get("elType") == "widget")
            settings = value.get("settings")
            if isinstance(settings, dict):
                for key in visible_keys:
                    content = settings.get(key)
                    if isinstance(content, str) and content.strip():
                        visible.append(re.sub(r"\s+", " ", content).strip())
            for key, child in value.items():
                if key not in {"settings", "styles"}:
                    walk(child)

    walk(data)
    return {"nodes": nodes, "widgets": widgets, "visible_text": visible}


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="http://localhost:8082/")
    parser.add_argument("--before-dir", required=True)
    parser.add_argument("--after-dir", required=True)
    parser.add_argument("--elementor-before", required=True)
    parser.add_argument("--elementor-after", required=True)
    parser.add_argument("--output", required=True)
    args = parser.parse_args()

    base = args.base_url.rstrip("/")
    before = json.loads((Path(args.before_dir) / "inventory.json").read_text(encoding="utf-8"))
    after = json.loads((Path(args.after_dir) / "inventory.json").read_text(encoding="utf-8"))
    result: dict = {
        "metrics": {"before": metrics(before), "after": metrics(after)},
        "checks": {},
    }

    home_status, home_document = fetch(base + "/")
    home_entities = schema_entities(home_document)
    organizations = [entity for entity in home_entities if has_type(entity, "CollegeOrUniversity")]
    organization = organizations[0] if organizations else {}
    result["checks"]["institution_schema"] = {
        "passed": bool(organization)
        and organization.get("name") == "SABER College"
        and organization.get("foundingDate") == "1972"
        and organization.get("telephone") == "+1-305-443-9170"
        and organization.get("address", {}).get("postalCode") == "33134",
        "type_count": len(organizations),
        "id": organization.get("@id"),
    }

    program_expectations = {
        "/saber_college_programs/professional-nursing-program/": (80, "PT1725H"),
        "/saber_college_programs/physical-therapist-assistant-program/": (76, "PT1785H"),
    }
    program_checks = {}
    for path, (credits, duration) in program_expectations.items():
        status, document = fetch(base + path)
        entities = schema_entities(document)
        programs = [entity for entity in entities if has_type(entity, "EducationalOccupationalProgram")]
        program = programs[0] if programs else {}
        program_checks[path] = {
            "passed": status == 200
            and str(program.get("numberOfCredits")) == str(credits)
            and program.get("timeToComplete") == duration
            and program.get("provider", {}).get("@id") == base + "/#organization",
            "name": program.get("name"),
            "credits": program.get("numberOfCredits"),
            "duration": program.get("timeToComplete"),
        }
    result["checks"]["program_schema"] = program_checks

    pairs = {
        "7-reasons-to-study-nursing-in-miami-fl": "7_razones_para_estudiar_enfermeria",
        "registered_nurse_in_miami_florida": "programa_de_enfermeria_en_miami",
        "nursing-program-earn-nln-cnea-accreditation": "programa-de-enfermeria-nln-cnea",
        "saber-college-pta-students-join-moving-day-miami": "saber-college-pta-camina-moving-day-miami",
        "get_ready_npte_pta_exam": "preparacion-examen-npte-pta-saber-college",
        "npte-exam-pta-saber-college": "examen-npte-pta-saber-college",
        "clinical-simulation-in-nursing-saber-college": "simulacion-clinica-en-enfermeria-saber-college",
        "study-nursing-in-miami-2026": "estudiar-enfermeria-en-miami-2026",
        "physical-therapist-assistant-role-explained": "rol-del-physical-therapist-assistant",
        "pavel_pugh_nln_cnea_appointment": "saber-college-dr-pavel-pugh-nombramiento-nln-cnea",
        "weekend-nursing-program": "programa-de-enfermeria-de-fin-de-semana-miami",
        "weekend-nursing-programs": "programas-enfermeria-fines-de-semana",
    }
    language_checks = []
    for english, spanish in pairs.items():
        for slug, language in ((english, "en-US"), (spanish, "es-US")):
            status, document = fetch(f"{base}/{slug}/")
            document_lang = re.search(r"<html\b[^>]*\blang=([\"'])(.*?)\1", document, re.I | re.S)
            hreflangs = dict(
                (language_code, href)
                for _, language_code, _, href in re.findall(
                    r"<link\b[^>]*\bhreflang=([\"'])(.*?)\1[^>]*\bhref=([\"'])(.*?)\3[^>]*>",
                    document,
                    re.I | re.S,
                )
            )
            language_checks.append(
                {
                    "slug": slug,
                    "passed": status == 200
                    and bool(document_lang)
                    and document_lang.group(2) == language
                    and set(hreflangs) == {"en-US", "es-US", "x-default"},
                    "lang": document_lang.group(2) if document_lang else "",
                    "hreflangs": hreflangs,
                }
            )
    result["checks"]["language_pairs"] = {
        "passed": all(item["passed"] for item in language_checks),
        "checked_pages": len(language_checks),
        "failures": [item for item in language_checks if not item["passed"]],
    }

    llms = (Path(args.after_dir) / "llms.txt").read_text(encoding="utf-8")
    llms_links = re.findall(r"^- \[[^]]+\]\(([^)]+)\)", llms, re.M)
    result["checks"]["llms_txt"] = {
        "passed": len(llms_links) == 26
        and "???" not in llms
        and "Home -" not in llms
        and all(link.startswith(base + "/") for link in llms_links),
        "curated_page_count": len(llms_links),
        "has_encoding_artifacts": "???" in llms,
        "has_generic_home_excerpt": "Home -" in llms,
    }

    before_elementor = elementor_stats(Path(args.elementor_before))
    after_elementor = elementor_stats(Path(args.elementor_after))
    result["checks"]["elementor_preservation"] = {
        "passed": before_elementor == after_elementor,
        "before_nodes": before_elementor["nodes"],
        "after_nodes": after_elementor["nodes"],
        "before_widgets": before_elementor["widgets"],
        "after_widgets": after_elementor["widgets"],
        "visible_text_unchanged": before_elementor["visible_text"] == after_elementor["visible_text"],
    }

    robot_agents = ["Googlebot", "GPTBot", "OAI-SearchBot", "ChatGPT-User", "ClaudeBot"]
    agent_results = {}
    for agent in robot_agents:
        status, _ = fetch(base + "/", agent)
        agent_results[agent] = status
    result["checks"]["crawler_access"] = {
        "passed": home_status == 200 and all(status == 200 for status in agent_results.values()),
        "status_by_agent": agent_results,
    }

    after_metrics = result["metrics"]["after"]
    named_checks_pass = all(
        result["checks"][name]["passed"]
        for name in (
            "institution_schema",
            "language_pairs",
            "llms_txt",
            "elementor_preservation",
            "crawler_access",
        )
    )
    program_checks_pass = all(item["passed"] for item in program_checks.values())
    result["passed"] = (
        named_checks_pass
        and program_checks_pass
        and after_metrics["non_200"] == 0
        and after_metrics["missing_descriptions"] == 0
        and after_metrics["missing_canonicals"] == 0
        and after_metrics["pages_without_exactly_one_h1"] == 0
        and after_metrics["invalid_json_ld_blocks"] == 0
        and after_metrics["missing_image_alt_attributes"] == 0
    )

    output = Path(args.output)
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(result, indent=2, ensure_ascii=False), encoding="utf-8")
    print(json.dumps(result, indent=2, ensure_ascii=False))


if __name__ == "__main__":
    main()
