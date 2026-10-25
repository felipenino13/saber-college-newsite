import hashlib
import json
import math
from pathlib import Path
from urllib.parse import unquote, urlparse

import numpy as np
from PIL import Image

PROJECT = Path(__file__).resolve().parents[1]
AUDIT = PROJECT / "reports" / "lighthouse-before-20261018" / "raw"
UPLOADS = PROJECT / "wordpress" / "wp-content" / "uploads"
REPORT = PROJECT / "reports" / "performance-image-sidecars-20261018.json"

representative = [
    "home-mobile-run1.report.json",
    "programs-mobile-run1.json",
    "professional-nursing-mobile-run1.json",
    "physical-therapist-assistant-mobile-run1.json",
    "admissions-tuition-mobile-run1.json",
    "student-services-mobile-run1.json",
    "general-mobile-run1.json",
    "lp-pnp-mobile-run1.json",
    "lp-pta-mobile-run1.json",
]


def sha256(path):
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def rgb_on_white(image):
    image = image.convert("RGBA")
    background = Image.new("RGBA", image.size, "white")
    return Image.alpha_composite(background, image).convert("RGB")


def psnr(original, encoded):
    source = np.asarray(rgb_on_white(original), dtype=np.float32)
    result = np.asarray(rgb_on_white(encoded), dtype=np.float32)
    mse = float(np.mean((source - result) ** 2))
    return 99.0 if mse == 0 else 20 * math.log10(255.0 / math.sqrt(mse))


targets = {}
for filename in representative:
    lhr = json.loads((AUDIT / filename).read_text(encoding="utf-8"))
    for item in lhr["audits"]["network-requests"]["details"]["items"]:
        if item.get("resourceType") != "Image":
            continue
        parsed = urlparse(item.get("url", ""))
        marker = "/wp-content/uploads/"
        if marker not in parsed.path:
            continue
        relative = unquote(parsed.path.split(marker, 1)[1])
        path = UPLOADS / Path(relative)
        if path.suffix.lower() not in {".png", ".jpg", ".jpeg"} or not path.is_file():
            continue
        targets[str(path)] = {
            "path": path,
            "requested_bytes": int(item.get("transferSize") or 0),
        }

results = []
for record in sorted(targets.values(), key=lambda item: item["path"].as_posix().lower()):
    source = record["path"]
    sidecar = Path(str(source) + ".webp")
    existed = sidecar.exists()
    original_bytes = source.stat().st_size
    with Image.open(source) as image:
        image.load()
        width, height = image.size
        mode = image.mode
        save_image = image.convert("RGBA") if "A" in image.getbands() else image.convert("RGB")
        use_lossless = original_bytes < 150_000
        quality = None if use_lossless else 92
        if use_lossless:
            save_image.save(sidecar, "WEBP", lossless=True, method=6, exact=True)
        else:
            save_image.save(sidecar, "WEBP", quality=quality, method=6, exact=True)
            with Image.open(sidecar) as encoded:
                score = psnr(image, encoded)
            if score < 38.5:
                quality = 96
                save_image.save(sidecar, "WEBP", quality=quality, method=6, exact=True)
                with Image.open(sidecar) as encoded:
                    score = psnr(image, encoded)
            if score < 38.5:
                quality = 98
                save_image.save(sidecar, "WEBP", quality=quality, method=6, exact=True)
        if sidecar.stat().st_size >= original_bytes * 0.95:
            sidecar.unlink()
            results.append({
                "source": str(source.relative_to(PROJECT)).replace("\\", "/"),
                "status": "skipped_no_meaningful_saving",
                "width": width,
                "height": height,
                "mode": mode,
                "original_bytes": original_bytes,
            })
            continue
        with Image.open(sidecar) as encoded:
            encoded.load()
            score = psnr(image, encoded)
            webp_width, webp_height = encoded.size
        webp_bytes = sidecar.stat().st_size
        results.append({
            "source": str(source.relative_to(PROJECT)).replace("\\", "/"),
            "sidecar": str(sidecar.relative_to(PROJECT)).replace("\\", "/"),
            "status": "recreated" if existed else "created",
            "encoding": "lossless" if use_lossless else "lossy",
            "quality": quality,
            "width": width,
            "height": height,
            "webp_width": webp_width,
            "webp_height": webp_height,
            "same_dimensions": [width, height] == [webp_width, webp_height],
            "original_bytes": original_bytes,
            "webp_bytes": webp_bytes,
            "saved_bytes": original_bytes - webp_bytes,
            "reduction_percent": round(100 * (original_bytes - webp_bytes) / original_bytes, 1),
            "psnr_db": round(score, 2),
            "source_sha256": sha256(source),
            "sidecar_sha256": sha256(sidecar),
        })

payload = {
    "generated_date": "2026-10-18",
    "policy": "Same pixel dimensions; small graphics use lossless WebP; larger images use quality 92, raised through 96 to 98 until PSNR reaches 38.5 dB when possible; originals retained.",
    "targets": len(targets),
    "created": sum(item["status"] == "created" for item in results),
    "recreated": sum(item["status"] == "recreated" for item in results),
    "skipped": sum(item["status"].startswith("skipped") for item in results),
    "original_bytes": sum(item.get("original_bytes", 0) for item in results),
    "served_webp_bytes": sum(item.get("webp_bytes", item.get("original_bytes", 0)) for item in results),
    "all_same_dimensions": all(item.get("same_dimensions", True) for item in results),
    "minimum_psnr_db": min((item["psnr_db"] for item in results if "psnr_db" in item), default=None),
    "images": results,
}
REPORT.write_text(json.dumps(payload, indent=2, ensure_ascii=False), encoding="utf-8")
print(json.dumps({key: value for key, value in payload.items() if key != "images"}, indent=2, ensure_ascii=False))
