# Hero image — SABER Survey

- **Fecha:** 2026-10-07
- **Página objetivo:** `/student-services/saber-survey/`
- **WordPress ID:** 904
- **Estado:** recurso local; no importado ni publicado en WordPress.

## Archivos

- `saber-survey-hero-master.png` — 2048 × 1152 px, relación 16:9.
- `saber-survey-hero-web.png` — 1600 × 900 px, relación 16:9, versión optimizada para web.
- Artefacto original de generación: `output/imagegen/saber-survey-hero-v1.png`.

## Generación

- Modelo: `gpt-image-2`
- Calidad: `high`
- Método: API de OpenAI mediante el CLI de la skill `imagegen`.
- Credencial: cargada localmente desde `.env.local`; no se registró en este archivo.

## Concepto

Una estudiante adulta completa una encuesta digital sobre costos de vida en un espacio tranquilo del campus. La tableta utiliza controles visuales abstractos y sin contenido legible. Una calculadora, una libreta cerrada, una botella reutilizable y la mochila aportan contexto cotidiano sin revelar información personal o financiera.

La escena comunica participación, responsabilidad, privacidad y colaboración con el proceso institucional. No representa respuestas específicas ni afirma que la encuesta sea anónima.

## Regla de roles

El modelo humano tomado de `src/img-referencias/vistas-modelos/` aparece exclusivamente como estudiante. No aparece como investigadora, administradora, integrante del personal, asesora financiera o profesional de salud.

Esta regla se mantiene para todas las futuras imágenes: los modelos de esa carpeta representan estudiantes o aspirantes; Sabi puede asumir funciones más amplias cuando el concepto lo requiera.

## Referencia utilizada

- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_03_13 AM - Copy.png`

## Prompt

```text
Use case: photorealistic-natural
Asset type: horizontal 16:9 website hero image for the SABER College student cost-of-living survey page.
Primary request: create a completely new candid campus scene using the supplied image only as the identity and appearance reference for one adult SABER College student. Show the Black adult student with long dark wavy hair inspired by Image 1 seated in a quiet, bright college study lounge while thoughtfully completing a digital student cost-of-living survey on an unbranded tablet. A small calculator, a closed navy notebook, a reusable water bottle, and a compact student backpack may sit nearby as secondary objects. The tablet interface should suggest a simple survey through abstract sliders, checkboxes, and neutral blocks, but it must contain no readable text, numbers, answers, names, IDs, email addresses, or financial information. The image should communicate student participation, care, responsibility, privacy, and helping the college understand students' everyday living needs without showing or implying any specific response.
Input images: Image 1 is the identity, facial appearance, skin tone, and long dark wavy hair reference for the adult student only. She must appear exclusively as a current student, never as an administrator, survey researcher, faculty member, staff member, financial advisor, or healthcare professional.
Scene/backdrop: luminous contemporary Miami college study lounge or academic commons with soft white and warm-ivory architecture, blue-tinted glass, large windows, restrained greenery, comfortable seating, and natural daylight; calm, private, organized, and clearly educational rather than a bank, home, employer office, or clinical setting.
Subject: exactly one foreground adult student. She wears polished student smart-casual clothing in navy, royal blue, and warm ivory with no logos, and her compact backpack is visible near the chair. She holds or uses the tablet naturally with focused, calm body language.
Style/medium: premium realistic editorial campus photography, candid and human, natural skin texture, credible anatomy and proportions, authentic fabric folds, polished without looking like staged stock photography.
Composition/framing: exact 16:9 landscape composition at eye level, medium-wide seated three-quarter framing, the student and tablet grouped near the center with comfortable breathing room; safe for responsive cropping inside the existing right-side hero visual area; face and both hands fully visible; tablet and supporting objects visible but secondary.
Lighting/mood: soft Miami morning daylight, thoughtful, calm, private, responsible, welcoming, and trustworthy.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with one restrained warm-yellow environmental accent.
Constraints: the supplied reference model retains a student role only; exactly one foreground adult; natural hands, scale, shadows, posture, and gaze; the tablet interface must contain only indistinct abstract shapes; no readable content anywhere in the image; no visible personal, academic, contact, or financial data; do not imply that the survey is anonymous.
Avoid: readable text, letters, numbers, logos, watermarks, school names, badges, IDs, student records, email addresses, addresses, signatures, dollar amounts, rent figures, utility bills, receipts, checks, cash, credit cards, bank statements, tax forms, survey answers, charts with values, medical procedures, patients, hospital settings, scrubs, stethoscopes, staff roles, celebratory poses, staged thumbs-up gestures, exaggerated smiles, distressed expressions, distorted hands, duplicated people, extra limbs, cluttered tables, heavy blue color grading.
```

## Validación

- Ambas versiones conservan una relación exacta de 16:9.
- El modelo de referencia aparece exclusivamente como estudiante.
- La mochila y el entorno refuerzan el contexto académico.
- La interfaz muestra solamente controles y bloques abstractos sin contenido legible.
- No aparecen nombres, ID, correo, respuestas, montos, facturas, tarjetas, efectivo ni información bancaria.
- La imagen no afirma que la encuesta sea anónima.
- Se revisaron rostro, manos, proporciones, tableta, calculadora y mochila.
- No se modificó ni se importó contenido en WordPress.

## SHA-256

- Master: `2BCDAF35CC8C86DB831170FD3DD2D081FED1D4603A52860727E8A88D86CD8B04`
- Web: `895832248BD1EE3A2CB498883B5011B0CB7F97F1FA669B12C6A2558F096C343E`
