import json
import sys
from pathlib import Path

sys.stdout.reconfigure(encoding="utf-8")

ROOT = Path(__file__).resolve().parent / "backups" / "lighthouse-accessibility-20261019"
TARGET_IDS = {
    "7daac76", "8d28c13", "29f746e",
    "b15c9ae", "e46950f", "6bc2b1d",
    "54fc4e9", "839119c",
}


def load(path):
    value = json.loads(path.read_text(encoding="utf-8-sig"))
    return json.loads(value) if isinstance(value, str) else value


def walk(nodes, page_id, trail=()):
    for node in nodes:
        node_id = node.get("id", "")
        widget = node.get("widgetType", "")
        settings = node.get("settings") or {}
        html = str(settings.get("html") or "")
        if node_id in TARGET_IDS or "paste" in html.lower() or "facebook.com/mysabercollege" in json.dumps(settings):
            printable = settings
            if html:
                position = html.lower().find("paste")
                printable = {
                    "html_length": len(html),
                    "contains_paste": position >= 0,
                    "paste_context": html[max(0, position - 320):position + 720] if position >= 0 else html[:600],
                    "other_setting_keys": sorted(key for key in settings if key != "html"),
                }
            print(json.dumps({
                "post_id": page_id,
                "trail": [*trail, node_id],
                "id": node_id,
                "elType": node.get("elType"),
                "widgetType": widget,
                "settings": printable,
            }, indent=2, ensure_ascii=False))
        walk(node.get("elements") or [], page_id, (*trail, node_id))


for post_id in (7, 16, 53, 312):
    print(f"\n### POST {post_id}")
    walk(load(ROOT / f"elementor-{post_id}-before.json"), post_id)
