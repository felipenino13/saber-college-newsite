#!/usr/bin/env python3
"""Create a reproducible crawl inventory for AI/SEO discovery checks."""

from __future__ import annotations

import argparse
import csv
import html
import json
import re
import urllib.error
import urllib.request
import xml.etree.ElementTree as ET
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import urljoin, urlparse


USER_AGENT = "SABER-College-AI-Indexation-Audit/1.0"


def fetch(url: str) -> tuple[int, str, dict[str, str]]:
    request = urllib.request.Request(url, headers={"User-Agent": USER_AGENT})
    try:
        with urllib.request.urlopen(request, timeout=30) as response:
            raw = response.read()
            charset = response.headers.get_content_charset() or "utf-8"
            return response.status, raw.decode(charset, "replace"), dict(response.headers.items())
    except urllib.error.HTTPError as error:
        raw = error.read()
        charset = error.headers.get_content_charset() or "utf-8"
        return error.code, raw.decode(charset, "replace"), dict(error.headers.items())


def strip_markup(value: str) -> str:
    value = re.sub(r"<script\b.*?</script>", " ", value, flags=re.I | re.S)
    value = re.sub(r"<style\b.*?</style>", " ", value, flags=re.I | re.S)
    value = re.sub(r"<[^>]+>", " ", value)
    return " ".join(html.unescape(value).split())


def attr_from_tag(tag: str, name: str) -> str:
    match = re.search(rf"\b{re.escape(name)}\s*=\s*([\"'])(.*?)\1", tag, re.I | re.S)
    return html.unescape(match.group(2)).strip() if match else ""


def first_meta(document: str, name: str) -> str:
    for tag in re.findall(r"<meta\b[^>]*>", document, re.I):
        if attr_from_tag(tag, "name").lower() == name.lower():
            return attr_from_tag(tag, "content")
    return ""


def canonical(document: str) -> str:
    for tag in re.findall(r"<link\b[^>]*>", document, re.I):
        rel = attr_from_tag(tag, "rel").lower().split()
        if "canonical" in rel:
            return attr_from_tag(tag, "href")
    return ""


def collect_schema_types(value, output: list[str]) -> None:
    if isinstance(value, dict):
        item_type = value.get("@type")
        if isinstance(item_type, str):
            output.append(item_type)
        elif isinstance(item_type, list):
            output.extend(str(item) for item in item_type)
        for child in value.values():
            collect_schema_types(child, output)
    elif isinstance(value, list):
        for child in value:
            collect_schema_types(child, output)


def parse_page(url: str, status: int, document: str) -> dict:
    title_match = re.search(r"<title\b[^>]*>(.*?)</title>", document, re.I | re.S)
    title = strip_markup(title_match.group(1)) if title_match else ""
    lang_match = re.search(r"<html\b([^>]*)>", document, re.I | re.S)
    language = attr_from_tag(lang_match.group(0), "lang") if lang_match else ""
    headings = []
    for level, value in re.findall(r"<h([1-6])\b[^>]*>(.*?)</h\1>", document, re.I | re.S):
        text = strip_markup(value)
        if text:
            headings.append({"level": int(level), "text": text})

    schema_types: list[str] = []
    invalid_schema_blocks = 0
    schema_blocks = re.findall(
        r"<script\b[^>]*type\s*=\s*([\"'])application/ld\+json\1[^>]*>(.*?)</script>",
        document,
        re.I | re.S,
    )
    for _, block in schema_blocks:
        try:
            collect_schema_types(json.loads(html.unescape(block)), schema_types)
        except json.JSONDecodeError:
            invalid_schema_blocks += 1

    links = []
    base_host = urlparse(url).netloc
    for tag in re.findall(r"<a\b[^>]*>", document, re.I | re.S):
        href = attr_from_tag(tag, "href")
        if href:
            links.append(urljoin(url, href))
    internal_links = sorted({link for link in links if urlparse(link).netloc == base_host})
    external_links = sorted({link for link in links if urlparse(link).netloc not in ("", base_host)})

    image_alt = []
    for tag in re.findall(r"<img\b[^>]*>", document, re.I | re.S):
        image_alt.append(attr_from_tag(tag, "alt"))

    visible_text = strip_markup(document)
    return {
        "url": url,
        "http_status": status,
        "title": title,
        "meta_description": first_meta(document, "description"),
        "meta_robots": first_meta(document, "robots"),
        "canonical": canonical(document),
        "language": language,
        "headings": headings,
        "h1_count": sum(1 for heading in headings if heading["level"] == 1),
        "schema_types": sorted(set(schema_types)),
        "schema_block_count": len(schema_blocks),
        "invalid_schema_block_count": invalid_schema_blocks,
        "internal_link_count": len(internal_links),
        "external_link_count": len(external_links),
        "image_count": len(image_alt),
        "missing_alt_count": sum(1 for value in image_alt if not value),
        "body_word_count": len(visible_text.split()),
    }


def sitemap_urls(base_url: str) -> list[str]:
    index_url = urljoin(base_url.rstrip("/") + "/", "sitemap_index.xml")
    status, document, _ = fetch(index_url)
    if status != 200:
        raise RuntimeError(f"Could not fetch {index_url}: HTTP {status}")

    namespace = {"sm": "http://www.sitemaps.org/schemas/sitemap/0.9"}
    root = ET.fromstring(document)
    sitemap_locations = [node.text.strip() for node in root.findall("sm:sitemap/sm:loc", namespace) if node.text]
    if not sitemap_locations:
        sitemap_locations = [index_url]

    output: list[str] = []
    for sitemap_url in sitemap_locations:
        status, sitemap_document, _ = fetch(sitemap_url)
        if status != 200:
            continue
        sitemap_root = ET.fromstring(sitemap_document)
        output.extend(node.text.strip() for node in sitemap_root.findall("sm:url/sm:loc", namespace) if node.text)
    return list(dict.fromkeys(output))


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="http://localhost:8082/")
    parser.add_argument("--output-dir", required=True)
    args = parser.parse_args()

    base_url = args.base_url.rstrip("/") + "/"
    output_dir = Path(args.output_dir)
    output_dir.mkdir(parents=True, exist_ok=True)

    endpoint_results = {}
    for endpoint in ("robots.txt", "sitemap_index.xml", "llms.txt"):
        url = urljoin(base_url, endpoint)
        status, body, headers = fetch(url)
        endpoint_results[endpoint] = {
            "url": url,
            "http_status": status,
            "content_type": headers.get("Content-Type", ""),
            "bytes": len(body.encode("utf-8")),
        }
        (output_dir / endpoint).write_text(body, encoding="utf-8")

    urls = sitemap_urls(base_url)
    pages = []
    for index, url in enumerate(urls, start=1):
        status, document, _ = fetch(url)
        pages.append(parse_page(url, status, document))
        print(f"[{index}/{len(urls)}] {status} {url}")

    result = {
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "base_url": base_url,
        "page_count": len(pages),
        "endpoints": endpoint_results,
        "pages": pages,
    }
    (output_dir / "inventory.json").write_text(
        json.dumps(result, indent=2, ensure_ascii=False), encoding="utf-8"
    )

    fields = [
        "url",
        "http_status",
        "title",
        "meta_description",
        "meta_robots",
        "canonical",
        "language",
        "h1_count",
        "schema_types",
        "schema_block_count",
        "invalid_schema_block_count",
        "internal_link_count",
        "external_link_count",
        "image_count",
        "missing_alt_count",
        "body_word_count",
    ]
    with (output_dir / "inventory.csv").open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=fields)
        writer.writeheader()
        for page in pages:
            row = {key: page[key] for key in fields}
            row["schema_types"] = ", ".join(page["schema_types"])
            writer.writerow(row)


if __name__ == "__main__":
    main()
