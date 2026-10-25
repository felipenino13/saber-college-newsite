# Semantic and Elementor review: program pages

Date: 2026-10-07  
Environment: local WordPress at `http://localhost:8082`  
Publication status: local only; nothing was uploaded or published to the production site.

## Scope

- `/saber_college_programs/` — page ID 30
- `/saber_college_programs/professional-nursing-program/` — page ID 870
- `/saber_college_programs/physical-therapist-assistant-program/` — page ID 872
- Shared Elementor form template `Form General` — template ID 2015

All changes were made in native Elementor container/widget settings. No page was replaced with static HTML, and the original Elementor node/widget counts were preserved.

## Changes

### Programs

- Marked the principal content groups as native `<section>` elements.
- Marked each program card as an `<article>`.
- Removed the oversized link that wrapped each complete card and linked the program heading instead, producing a clearer accessible link name.
- Corrected the heading outline from `H1 + H3 + H3 + H3` to `H1 + H2 + H3 + H3` without changing wording or appearance.
- Linked the existing words `career services` to `/student-services/career-services/`.

### Professional Nursing

- Marked eight principal Elementor containers as `<section>` elements.
- Kept one H1 followed by seven H2 headings.
- Changed the visual-only hero emphasis from `<strong>` to `<b>` while keeping meaningful emphasis in the responsibilities introduction.
- Added contextual links from the existing wording to the official NCLEX, Council on Occupational Education, and NLN CNEA pages.
- Changed the form accessible name from `New Form` to `Request Information`.
- Repaired the embedded Elementor form script: removed an invalid top-level `return`, removed an out-of-scope session variable reference, and retained a safe phone formatter so the intended input listener does not call an undefined function.

### Physical Therapist Assistant

- Marked seven principal Elementor containers as `<section>` elements.
- Kept one H1 and six H2 headings, and promoted `Credit/Clock Hour Allocation Summary` from a bold paragraph to an H3.
- Changed the visual-only hero emphasis from `<strong>` to `<b>`.
- Added contextual links from the existing wording to the official BLS PTA and FSBPT NPTE pages.
- Changed the shared form accessible name from `New Form` to `Request Information`.
- Repaired the same invalid script statements in the shared Elementor form template.

## Validation

- All three page URLs return HTTP 200.
- Visible text hashes are identical before and after on all three pages.
- No nested anchors, empty `href="#"` links, or duplicate IDs were found in the page content.
- Final semantic counts:
  - Programs: 1 H1, 1 H2, 2 H3, 2 sections, 2 articles.
  - Professional Nursing: 1 H1, 7 H2, 8 sections.
  - PTA: 1 H1, 6 H2, 1 H3, 7 sections.
- Form labels and field IDs are unique; the two program forms expose `aria-label="Request Information"`.
- Elementor integrity is unchanged:
  - Programs: 38 nodes / 18 widgets.
  - Professional Nursing: 86 nodes / 43 widgets.
  - PTA: 70 nodes / 33 widgets.
  - Shared form template: 6 nodes / 5 widgets.
- The updater was run twice and produced structurally identical Elementor JSON on the second run.
- Mobile checks at 390 × 844 found no horizontal document overflow.
- Fresh browser loads of the Nursing and PTA pages produced no JavaScript console errors after the form-script repair.

## Readability emphasis

At the user's request, selected phrases inside longer paragraphs were marked with native `<strong>` tags. The emphasis is limited to concepts that help a reader scan the page: career outcomes, practical training, clinical competencies, degree distinctions, accreditation status, and important student responsibilities. Complete paragraphs and time-sensitive salary/growth statistics were intentionally left unbolded.

- Programs: 5 semantic highlights.
- Professional Nursing: 15 new semantic highlights, plus 1 meaningful `<strong>` element already present.
- PTA: 10 new semantic highlights, plus 2 meaningful `<strong>` elements already present.
- Visible-text hashes remain identical after adding the emphasis.
- No nested `<strong>` elements were produced, and the updater remains idempotent.
- Recovery snapshot immediately before this refinement: `reports/backups/program-pages-bold-20261007/`.

## Recovery files

The complete before/after Elementor data, rendered HTML, and post backups are in:

`reports/backups/program-pages-semantic-20261007/`

The repeatable updater is:

`scripts/update-program-pages-semantic-markup.php`

Desktop visual captures:

- `reports/programs-semantic-after-desktop-20261007.png`
- `reports/professional-nursing-semantic-after-desktop-20261007.png`
- `reports/pta-semantic-after-desktop-20261007.png`

Desktop captures after the readability refinement:

- `reports/programs-bold-after-desktop-20261007.png`
- `reports/professional-nursing-bold-after-desktop-20261007.png`
- `reports/pta-bold-after-desktop-20261007.png`

## Official destinations added

- NCLEX: `https://www.nclex.com/index.page`
- Council on Occupational Education: `https://council.org/`
- NLN CNEA: `https://cnea.nln.org/`
- BLS Physical Therapist Assistants and Aides: `https://www.bls.gov/ooh/healthcare/physical-therapist-assistants-and-aides.htm`
- FSBPT National Physical Therapy Examination: `https://www.fsbpt.org/secondary-pages/exam-candidates/national-exam-npte`
