# Home semantic markup update

Date: 2026-10-07  
Local URL: `http://localhost:8082/`  
WordPress page: `Home` (`ID 7`)

## Scope

The home page was updated through its native Elementor data. No section was replaced with static HTML, and all existing containers, widgets, accordions, forms, buttons, images, video, review shortcode, news loop and social feed remain editable in Elementor.

The visible wording was not changed. Normalized visible text before and after the update has the same SHA-256 value:

`328cb243ecb6aa38f5f0785b7c3726e480e15aabe30105a3a3380d61e7ab0acf`

## Applied changes

### Heading hierarchy

- Preserved the single `H1`: `Your Future in Healthcare Starts Here`.
- Changed `Why Choose SABER?` from a paragraph to `H2`.
- Changed `There are a lot of reasons why you should choose Saber College.` from `H3` to an introductory paragraph.
- Final structure: one `H1`, ten `H2` elements and nine `H3` elements.

### Semantic containers

- Added eleven native Elementor `section` tags to the page's main content blocks.
- Marked the announcement bar as an `aside`.
- Marked the two program cards as `article` elements.
- Preserved all Elementor node IDs, styles and responsive settings.

### Links

- Linked both program-card `H3` titles to their program pages.
- Connected `More Blog SABER` to `/saber_college_blog/`.
- Connected `Apply Now` to `/apply-to-saber-college/`.
- Replaced all five `href="#"` links in the FAQ with real internal or source destinations.
- Added contextual internal links for Career Services, Academic Affairs and the student-life gallery.
- Added source links to the official BLS Registered Nurses and PTA pages, CAPTE and O*NET.
- All eight affected local destinations returned HTTP `200` after the change.

### Emphasis markup

- Preserved `strong` for the deadline, principal hero benefit and highlighted employment figures.
- Replaced nine visually bold key terms with `b`, retaining their appearance without assigning strong semantic importance.
- Final count in the main content: six `strong`, nine `b`, zero `em`.

### Request Information form

- Changed the form's accessible name from `New Form` to `Request Information`.
- Changed the duplicate First Name and Last Name field IDs to `first_name` and `last_name`.
- Confirmed that each visible label now points to its corresponding unique input.
- The form remains the original Elementor Form widget.

## Elementor integrity

Before and after the update, the page contains exactly:

- 154 Elementor nodes.
- 75 Elementor widgets.
- 55 Heading widgets.
- 1 Form widget.
- 3 Nested Accordion widgets.
- 1 Loop Grid widget.
- 2 Atomic Button widgets.
- 4 Atomic Image widgets.

No widget type or Elementor node was removed.

## Validation

- Home response: HTTP `200`.
- Page title unchanged: `Healthcare Career Training in Miami | SABER College`.
- Canonical, robots and existing Rank Math schema retained.
- Zero remaining links with `href="#"` in the main content.
- All visible wording unchanged.
- Elementor CSS cache regenerated.
- Desktop and mobile visual review completed.
- Browser console: zero warnings or errors during the final review.

Screenshots:

- `reports/home-semantic-after-desktop-20261007.png`
- `reports/home-semantic-after-mobile-20261007.png`

## Recovery files

The complete Elementor payload and rendered HTML were saved before and after the update:

- `reports/backups/home-semantic-20261007/page-7-elementor-before.json`
- `reports/backups/home-semantic-20261007/page-7-elementor-after.json`
- `reports/backups/home-semantic-20261007/page-7-rendered-before.html`
- `reports/backups/home-semantic-20261007/page-7-rendered-after.html`
- `reports/backups/home-semantic-20261007/page-7-post-before.json`

The repeatable update script is:

- `scripts/update-home-semantic-markup.php`

No production site or WordPress installation outside the local project was modified.
