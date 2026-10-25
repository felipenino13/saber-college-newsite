import hashlib
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent
TARGETS = {
    "2015": "518a0bf",
    "7": "29f746e",
}


def load(name):
    return json.loads((ROOT / name).read_text(encoding="utf-8-sig"))


def walk(nodes):
    for node in nodes:
        if not isinstance(node, dict):
            continue
        yield node
        yield from walk(node.get("elements", []))


def normalized(data, target_id):
    clone = json.loads(json.dumps(data))
    found = False
    for node in walk(clone):
        if node.get("id") == target_id:
            node.setdefault("settings", {})["html"] = "__TARGET_HANDLER__"
            found = True
    if not found:
        raise RuntimeError(f"Target widget {target_id} not found")
    return clone


def forms(data):
    return [
        node.get("settings", {})
        for node in walk(data)
        if node.get("widgetType") == "form"
    ]


report = {"targets": {}}
for post_id, widget_id in TARGETS.items():
    before = load(f"elementor-{post_id}-before.json")
    after = load(f"elementor-{post_id}-after.json")
    before_nodes = list(walk(before))
    after_nodes = list(walk(after))
    before_target = next(n for n in before_nodes if n.get("id") == widget_id)
    after_target = next(n for n in after_nodes if n.get("id") == widget_id)
    before_html = before_target.get("settings", {}).get("html", "")
    after_html = after_target.get("settings", {}).get("html", "")
    report["targets"][post_id] = {
        "widget_id": widget_id,
        "node_count_before": len(before_nodes),
        "node_count_after": len(after_nodes),
        "non_handler_elementor_data_unchanged": normalized(before, widget_id) == normalized(after, widget_id),
        "form_settings_unchanged": forms(before) == forms(after),
        "old_selector_removed": 'querySelector("#form-field-program")' not in after_html,
        "checked_radio_selector_present": 'input[name="form_fields[program]"]:checked' in after_html,
        "null_guard_present": "program &&" in after_html and "if (!program ||" in after_html,
        "script_sha256_before": hashlib.sha256(before_html.encode()).hexdigest(),
        "script_sha256_after": hashlib.sha256(after_html.encode()).hexdigest(),
    }

(ROOT / "structural-verification.json").write_text(
    json.dumps(report, indent=2), encoding="utf-8"
)
print(json.dumps(report, indent=2))
