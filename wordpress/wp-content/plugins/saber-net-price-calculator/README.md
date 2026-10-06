# SABER Net Price Calculator

This plugin packages the current standalone SABER College Net Price Calculator for a staged WordPress migration.

## Current scope

- The original application is stored unchanged in `app/Net-Price-Calculator.html`.
- `assets/saber-design-system.css` adds the approved SABER visual language at runtime without modifying the legacy calculations or copy.
- Nunito is packaged locally under the SIL Open Font License so rendering does not depend on Google Fonts.
- WordPress serves it at `/saber-net-price-calculator-app/`.
- The implementation route sends `X-Robots-Tag: noindex, nofollow` so it cannot compete with the canonical page.
- `/general/net-price-calculator/` remains the public, canonical WordPress URL.
- `[saber_net_price_calculator]` embeds the application in Elementor through a responsive same-origin frame.
- `?embed=1` removes the application's duplicate header while preserving the standalone technical route.
- Rank Math redirects `/Net-Price-Calculator.html` to the canonical WordPress page with status `301`.

The original calculator data and formulas remain unchanged. Updating the regulatory data year requires an approved institutional source.

## Source

- URL: `https://sabercollege.edu/Net-Price-Calculator.html`
- Retrieved: `2026-10-05`
- SHA-256: `4DC8F93FDE0AF790DA08D280C7815383EC6558C5B09C3A081E3C98DDFC364ECF`
