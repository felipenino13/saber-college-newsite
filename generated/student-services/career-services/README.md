# Hero image — Career Services

- **Fecha:** 2026-10-07
- **Página objetivo:** `/student-services/career-services/`
- **WordPress ID:** 900
- **Estado:** recurso local; no importado ni publicado en WordPress.

## Archivos

- `career-services-hero-master.png` — 2048 × 1152 px, relación 16:9.
- `career-services-hero-web.png` — 1600 × 900 px, relación 16:9, versión optimizada para web.
- Artefacto original de generación: `output/imagegen/career-services-hero-v1.png`.

## Generación

- Modelo: `gpt-image-2`
- Calidad: `high`
- Método: API de OpenAI mediante el CLI de la skill `imagegen`.
- Credencial: cargada localmente desde `.env.local`; no se registró en este archivo.

## Concepto

Una estudiante adulta practica para una futura entrevista con una career coach en un espacio luminoso del campus. La estudiante lleva ropa apropiada para preparación profesional, un portafolio y una mochila. La coach utiliza una hoja de estructura genérica y una tableta con elementos abstractos para orientar la conversación.

La escena representa acompañamiento individual, preparación de currículum y cover letter, práctica de entrevistas y desarrollo de habilidades de búsqueda de empleo. No representa una entrevista con un empleador, una oferta laboral, una contratación o una garantía de colocación.

## Regla de roles

El modelo humano tomado de `src/img-referencias/vistas-modelos/` aparece exclusivamente como estudiante preparándose para su futuro profesional. No aparece como graduada, empleada o profesional de salud. La career coach fue creada como una persona nueva y visualmente distinta de la referencia.

Esta regla se mantiene para todas las futuras imágenes: los modelos de esa carpeta representan estudiantes o aspirantes; Sabi puede asumir funciones más amplias cuando el concepto lo requiera.

## Referencia utilizada

- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_03_24 AM - Copy (1).png`

## Prompt

```text
Use case: photorealistic-natural
Asset type: horizontal 16:9 website hero image for the SABER College Career Services page.
Primary request: create a completely new candid career-readiness coaching scene using the supplied image only as the identity and appearance reference for one adult SABER College student. Show the brunette adult student inspired by Image 1 practicing for a future interview with a distinct, newly generated female career coach in a bright contemporary college Career Services area. The student sits upright with a simple navy portfolio and a compact backpack beside her chair, while the career coach offers constructive guidance using a blank resume layout and an unbranded tablet with abstract checklist shapes. Their natural conversation should communicate one-on-one career guidance, resume and cover-letter preparation, interview confidence, job-search skills, professional presentation, and support throughout the student's journey without presenting an actual employer interview, job offer, placement, employment guarantee, or professional healthcare role.
Input images: Image 1 is the identity, facial appearance, skin tone, and long brown hair reference for the adult student only. She must appear exclusively as a current student preparing for her future career, never as a graduate, employee, healthcare professional, faculty member, administrator, or Career Services staff member. The career coach must be a clearly distinct newly generated middle-aged woman with short natural hair and no facial resemblance to the reference student.
Scene/backdrop: luminous contemporary Miami college Career Services or interview-practice room with soft white and warm-ivory architecture, blue-tinted glass, large windows, restrained greenery, two comfortable chairs, and a small clean table; clearly located on a college campus rather than an employer office, hospital, clinic, or corporate boardroom.
Subject: exactly two foreground adults. The reference student wears polished interview-preparation clothing: a warm-ivory blouse, navy trousers, and a simple royal-blue cardigan, with a student portfolio and backpack that make her role clear. The distinct career coach wears a navy blazer over a pale-blue blouse and neutral trousers. The coach points gently toward the blank resume layout while the student listens and prepares to respond.
Style/medium: premium realistic editorial campus photography, candid and human, natural skin texture, credible anatomy and proportions, authentic fabric folds, polished without looking like staged corporate stock photography.
Composition/framing: exact 16:9 landscape composition at eye level, medium-wide seated three-quarter framing, both people grouped near the center with comfortable breathing room; safe for responsive cropping inside the existing right-side hero image panel; both faces and all hands fully visible; portfolio, backpack, tablet, and blank resume visible but secondary.
Lighting/mood: soft Miami morning daylight, encouraging, focused, practical, confident, optimistic, and trustworthy.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with one restrained warm-yellow environmental accent.
Constraints: the supplied reference model retains a student role only; the career coach is a new and visually distinct person; exactly two foreground adults; natural eye contact, gestures, hands, scale, shadows, and posture; all tablet screens and papers must be blank or contain only indistinct abstract shapes; no text anywhere in the image; no visible personal information, employer information, or job outcome.
Avoid: readable text, letters, numbers, logos, watermarks, school names, employer branding, badges, IDs, detailed resumes, cover letters, signatures, job offers, employment contracts, acceptance letters, diplomas, graduation caps, medical procedures, patients, hospital settings, stethoscopes, scrubs, lab coats, staff uniforms, handshakes, staged thumbs-up poses, celebratory hiring gestures, exaggerated smiles, distressed expressions, distorted hands, duplicated people, extra limbs, cluttered desks, heavy blue color grading.
```

## Validación

- Ambas versiones conservan una relación exacta de 16:9.
- El modelo de referencia aparece exclusivamente como estudiante en preparación profesional.
- La career coach es una persona nueva y claramente distinta.
- La mochila y el portafolio refuerzan el contexto estudiantil.
- La hoja y la tableta muestran solamente estructuras genéricas sin información personal o empresarial legible.
- No aparecen ofertas, contratos, empleadores, celebraciones de contratación ni garantías de colocación.
- Se revisaron rostros, manos, proporciones, tableta, hoja, portafolio y mochila.
- No se modificó ni se importó contenido en WordPress.

## SHA-256

- Master: `03BF9ED33B099C0050C1B55C880006997DCDF44399061457E461A09C516C5AF7`
- Web: `7D44B0106B76C9867557E3BBDF9B59E647E1499AE57685FC9D8FE8FF8C877D5F`
