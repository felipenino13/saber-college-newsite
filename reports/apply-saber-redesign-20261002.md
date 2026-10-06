# Apply To SABER College - Redesign Report

**Date:** 2026-10-02  
**WordPress page:** 883  
**URL:** `http://localhost:8082/apply-to-saber-college/`  
**Design reference:** SABER Design System 0.2 / Elementor library

## Result

The page was rebuilt as a clear five-step admissions journey using native Elementor Flexbox containers and widgets. The approved global header and footer were not edited.

- Centered Rank Math breadcrumb to protect the logo clearance.
- Path Light hero using the existing SABER College campus image.
- Introductory statement separated from the process content.
- Five application stages presented with alternating editorial surfaces and responsive list cards.
- Assistance and final action content arranged as a high-contrast closing pair.
- Desktop and mobile layouts have no horizontal overflow.

No new form or link was invented. The page keeps its existing informational function and all existing copy.

## Preservation checks

All checks passed after the redesign:

- 35 original content and list fields unchanged byte-for-byte.
- Rank Math metadata unchanged.
- WordPress `post_content` unchanged.
- Home page 7 unchanged.
- Header template 16 unchanged.
- Footer template 53 unchanged.
- All 18 original list items remain present.

## Elementor structure

- 44 native widgets.
- Widget types: `shortcode`, `divider`, `heading`, `text-editor`, `image`, `icon`, `icon-list`.
- Design assets: Path Light 2064, Path Warm 2065 and Path Navy 2067.
- Campus image: 1699.
- Page CSS is limited to responsive list grids, focus visibility and small widget spacing rules.

## Artifacts

- Backup: `database/elementor-backups/apply-saber-883-before-20261002-131314.json`
- Machine-readable result: `reports/apply-saber-redesign-result-20261002.json`
- Desktop screenshot: `reports/apply-saber-desktop-20261002.png`
- Build script: `scripts/redesign-apply-saber.php`
- Verification script: `scripts/verify-apply-saber.php`
