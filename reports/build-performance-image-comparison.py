import json
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

PROJECT = Path(__file__).resolve().parents[1]
manifest = json.loads((PROJECT / "reports" / "performance-image-sidecars-20261018.json").read_text(encoding="utf-8"))
items = [item for item in manifest["images"] if item.get("sidecar")]
items.sort(key=lambda item: item["original_bytes"], reverse=True)
items = items[:10]

thumb_w, thumb_h = 380, 215
label_h = 54
canvas = Image.new("RGB", (thumb_w * 2 + 60, (thumb_h + label_h) * len(items) + 40), "#e9f0f5")
draw = ImageDraw.Draw(canvas)
font = ImageFont.load_default(size=16)
small = ImageFont.load_default(size=13)


def fit(path):
    with Image.open(path) as source:
        image = source.convert("RGBA")
        background = Image.new("RGBA", image.size, "white")
        image = Image.alpha_composite(background, image).convert("RGB")
    image.thumbnail((thumb_w, thumb_h), Image.Resampling.LANCZOS)
    frame = Image.new("RGB", (thumb_w, thumb_h), "white")
    frame.paste(image, ((thumb_w - image.width) // 2, (thumb_h - image.height) // 2))
    return frame


for row, item in enumerate(items):
    y = 20 + row * (thumb_h + label_h)
    original = PROJECT / item["source"]
    webp = PROJECT / item["sidecar"]
    canvas.paste(fit(original), (20, y))
    canvas.paste(fit(webp), (40 + thumb_w, y))
    name = original.name[:60]
    draw.text((20, y + thumb_h + 7), f"Original · {name}", fill="#082b55", font=font)
    draw.text((20, y + thumb_h + 29), f"{item['original_bytes']/1024:.0f} KiB", fill="#52677b", font=small)
    draw.text((40 + thumb_w, y + thumb_h + 7), "WebP servido", fill="#082b55", font=font)
    draw.text(
        (40 + thumb_w, y + thumb_h + 29),
        f"{item['webp_bytes']/1024:.0f} KiB · {item['reduction_percent']:.1f}% menos · {item['width']}×{item['height']}",
        fill="#52677b",
        font=small,
    )

output = PROJECT / "reports" / "performance-image-quality-comparison-20261018.jpg"
canvas.save(output, quality=92, optimize=True)
print(output)
