# Hero image — Apply to SABER College

- **Fecha:** 2026-10-07
- **Página objetivo:** `/apply-to-saber-college/`
- **Estado:** recurso local; no importado ni publicado en WordPress.

## Archivos

- `apply-saber-hero-master.png` — 2048 × 1152 px, relación 16:9.
- `apply-saber-hero-web.png` — 1600 × 900 px, relación 16:9, versión optimizada para web.
- Artefacto original de generación: `output/imagegen/apply-saber-hero-v1.png`.

## Generación

- Modelo: `gpt-image-2`
- Calidad: `high`
- Método: API de OpenAI mediante el CLI de la skill `imagegen`.
- Credencial: cargada localmente desde `.env.local`; no se registró en este archivo.

## Concepto

Escena editorial y natural en un área luminosa de bienvenida o admisiones de SABER College. Dos aspirantes adultas comienzan el proceso de solicitud: una completa información en un portátil y la otra lleva una carpeta. Un asesor de admisiones, creado como una persona nueva y claramente distinta de los modelos de referencia, las orienta con una tableta.

La escena acompaña el contenido de la página: iniciar la solicitud, visitar el campus, recibir orientación y avanzar con preparación. No representa aceptación, elegibilidad financiera ni resultados garantizados.

## Regla de roles

Todos los modelos humanos tomados de `src/img-referencias/vistas-modelos/` tienen exclusivamente el rol de estudiantes o aspirantes. En esta imagen, las dos personas basadas en referencias son aspirantes. El asesor de admisiones no está basado en ninguna de ellas. Sabi puede asumir roles más amplios en futuras composiciones cuando el concepto lo justifique.

## Referencias utilizadas

- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 09_55_23 AM - Copy.png`
- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_49_50 AM - Copy (1).png`

## Prompt

```text
Use case: photorealistic-natural
Asset type: horizontal 16:9 website hero image for the Apply to SABER College page.
Primary request: create a completely new, candid campus application scene using the supplied images only as visual references for two adult prospective students. Show the Latina prospective student inspired by Image 1 and the Black prospective student with short natural twists inspired by Image 2 beginning their application at a bright SABER College welcome area. One student is seated and entering information on an unbranded laptop while the other stands beside her holding a simple navy portfolio. A distinct male admissions counselor, newly created and not based on either reference model, stands nearby and calmly guides them using a small blank tablet. The image should communicate taking the next step, a clear application journey, an in-person campus visit, personal support, preparation, confidence, and forward momentum without implying acceptance or financial-aid eligibility.
Input images: Image 1 is the appearance and long dark wavy hair reference for the Latina adult prospective student. Image 2 is the appearance and short natural twists reference for the Black adult prospective student. Both referenced people must appear only as students/applicants. The admissions counselor must be a clearly different newly generated person with no facial resemblance to either reference.
Scene/backdrop: luminous contemporary Miami college welcome or application area with soft white and warm-ivory architecture, large windows, subtle glass, restrained greenery, a clean application table, and bright natural daylight; inviting and organized, not a corporate office.
Style/medium: premium realistic editorial campus photography, candid and human, natural skin texture, credible anatomy and proportions, authentic fabric folds, polished without looking like staged stock photography.
Composition/framing: exact 16:9 landscape composition at eye level, medium-wide three-quarter body framing, the three adults arranged naturally around the application table with balanced breathing room; suitable for responsive cropping inside the existing right-side hero image panel; faces, hands, laptop, tablet, and portfolio fully visible.
Lighting/mood: soft Miami morning light, supportive, purposeful, optimistic, welcoming and trustworthy.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with one restrained warm-yellow environmental accent.
Constraints: exactly three foreground adults; two prospective students based on the references, both in polished student smart-casual navy, royal-blue and warm-ivory clothing with no logos; one distinct male admissions counselor in a navy blazer or polo with warm-ivory trousers and no logo; the reference models retain student/applicant roles only; natural eye contact, hands, scale and shadows; all screens and papers blank or unreadable; no text in the image.
Avoid: readable text, logos, watermarks, badges, IDs, passports, Social Security information, signatures, acceptance letters, diplomas, graduation caps, healthcare procedures, hospital setting, stethoscopes, scrubs on the admissions counselor, giant flags, handshakes, staged thumbs-up poses, exaggerated smiles, distorted hands, duplicated people, extra limbs, cluttered desk, heavy blue color grading.
```

## Validación

- Ambas versiones conservan una relación exacta de 16:9.
- Los dos modelos de referencia aparecen como estudiantes o aspirantes.
- El asesor es una persona distinta y no reutiliza la identidad visual de los modelos.
- No hay texto legible, logotipos, datos personales, cartas de aceptación ni promesas de ayuda financiera.
- Se revisaron rostros, manos, proporciones y objetos principales.
- No se modificó ni se importó contenido en WordPress.

## SHA-256

- Master: `5F43EEF372A4B87400BA7D2A65BDAE6D4D58899E5FC7B7E9BCB5C823ED7A509D`
- Web: `54B8E0AD030F016A651BC7F284E43B39E6FD79A592C8B84BD648422308366FCD`
