#!/usr/bin/env python3
"""Build the versioned SABI PDF knowledge bundle used by WordPress."""

from __future__ import annotations

import hashlib
import json
import re
from datetime import datetime, timezone
from pathlib import Path

from pypdf import PdfReader


PLUGIN_DIR = Path(__file__).resolve().parents[1]
PROJECT_DIR = PLUGIN_DIR.parents[3]
OUTPUT_FILE = PLUGIN_DIR / "knowledge" / "index.json"
CHUNK_SIZE = 1100
CHUNK_OVERLAP = 160

SOURCES = (
    {
        "source_key": "catalog-2026",
        "path": "wordpress/wp-content/uploads/2026/04/School-Catalog-4-22-2026-V2.pdf",
    },
    {
        "source_key": "nursing-handbook-2025",
        "path": "wordpress/wp-content/uploads/2026/02/PROFESSIONAL-NURSING-PROGRAM-STUDENT-HANDBOOK-7-1-2025.pdf",
    },
    {
        "source_key": "pta-handbook-2025",
        "path": "wordpress/wp-content/uploads/2026/02/PTA-2025-2026-PTA-Student-Handbook.pdf",
    },
)


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as stream:
        for block in iter(lambda: stream.read(1024 * 1024), b""):
            digest.update(block)
    return digest.hexdigest()


def clean_text(value: str) -> str:
    value = value.replace("\u00ad", "").replace("\x00", " ")
    value = re.sub(r"(?<=\w)-\s*\n\s*(?=\w)", "", value)
    lines = [re.sub(r"[ \t]+", " ", line).strip() for line in value.splitlines()]
    paragraphs: list[str] = []
    current: list[str] = []
    for line in lines:
        if not line:
            if current:
                paragraphs.append(" ".join(current))
                current = []
            continue
        current.append(line)
    if current:
        paragraphs.append(" ".join(current))
    return "\n\n".join(paragraphs).strip()


def split_long_text(text: str, size: int) -> list[str]:
    pieces: list[str] = []
    remaining = text.strip()
    while len(remaining) > size:
        split_at = remaining.rfind(" ", 0, size)
        if split_at < int(size * 0.6):
            split_at = size
        pieces.append(remaining[:split_at].strip())
        remaining = remaining[split_at:].strip()
    if remaining:
        pieces.append(remaining)
    return pieces


def chunk_page(text: str) -> list[str]:
    paragraphs = []
    for paragraph in text.split("\n\n"):
        paragraphs.extend(split_long_text(paragraph, CHUNK_SIZE))

    chunks: list[str] = []
    current = ""
    for paragraph in paragraphs:
        candidate = f"{current}\n\n{paragraph}".strip() if current else paragraph
        if current and len(candidate) > CHUNK_SIZE:
            chunks.append(current)
            overlap = current[-CHUNK_OVERLAP:]
            overlap = overlap[overlap.find(" ") + 1 :] if " " in overlap else overlap
            current = f"{overlap}\n\n{paragraph}".strip()
        else:
            current = candidate
    if current:
        chunks.append(current)
    return chunks


def heading_for(text: str, page_number: int) -> str:
    first = re.split(r"[\n.!?]", text, maxsplit=1)[0].strip()
    if 4 <= len(first) <= 120:
        return first
    return f"Page {page_number}"


def extract_source(definition: dict[str, str]) -> dict[str, object]:
    path = PROJECT_DIR / definition["path"]
    if not path.is_file():
        raise FileNotFoundError(path)

    reader = PdfReader(path)
    chunks: list[dict[str, object]] = []
    chunk_index = 0
    for page_number, page in enumerate(reader.pages, start=1):
        text = clean_text(page.extract_text() or "")
        if not text:
            continue
        for content in chunk_page(text):
            chunks.append(
                {
                    "index": chunk_index,
                    "page": page_number,
                    "heading": heading_for(content, page_number),
                    "content": content,
                    "hash": hashlib.sha256(content.encode("utf-8")).hexdigest(),
                }
            )
            chunk_index += 1

    return {
        "source_key": definition["source_key"],
        "path": definition["path"],
        "checksum": sha256_file(path),
        "page_count": len(reader.pages),
        "chunk_count": len(chunks),
        "chunks": chunks,
    }


def main() -> None:
    payload = {
        "schema_version": "1.0.0",
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "chunk_size": CHUNK_SIZE,
        "chunk_overlap": CHUNK_OVERLAP,
        "sources": [extract_source(source) for source in SOURCES],
    }
    OUTPUT_FILE.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT_FILE.write_text(
        json.dumps(payload, ensure_ascii=False, separators=(",", ":")),
        encoding="utf-8",
    )
    print(
        json.dumps(
            {
                "output": str(OUTPUT_FILE),
                "sources": len(payload["sources"]),
                "pages": sum(source["page_count"] for source in payload["sources"]),
                "chunks": sum(source["chunk_count"] for source in payload["sources"]),
            }
        )
    )


if __name__ == "__main__":
    main()
