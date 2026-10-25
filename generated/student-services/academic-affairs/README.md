# Hero image — Academic Affairs

- **Fecha:** 2026-10-07
- **Página objetivo:** `/student-services/academic-affairs/`
- **WordPress ID:** 896
- **Estado:** recurso local; no importado ni publicado en WordPress.

## Archivos

- `academic-affairs-hero-master.png` — 2048 × 1152 px, relación 16:9.
- `academic-affairs-hero-web.png` — 1600 × 900 px, relación 16:9, versión optimizada para web.
- Artefacto original de generación: `output/imagegen/academic-affairs-hero-v1.png`.

## Generación

- Modelo: `gpt-image-2`
- Calidad: `high`
- Método: API de OpenAI mediante el CLI de la skill `imagegen`.
- Credencial: cargada localmente desde `.env.local`; no se registró en este archivo.

## Concepto

Una estudiante adulta de un programa de salud revisa su planificación académica con una asesora en un espacio luminoso de apoyo estudiantil. La estudiante señala una parte de una secuencia visual abstracta mientras la asesora la acompaña con una tableta. La escena representa orientación personalizada, planificación de cursos, seguimiento del progreso y apoyo temprano para mantenerse encaminada.

El tablero, la tableta y el cuaderno no contienen información legible. No se muestran expedientes, calificaciones, asistencia, datos personales ni promesas de resultados académicos.

## Regla de roles

El modelo humano tomado de `src/img-referencias/vistas-modelos/` aparece exclusivamente como estudiante. Sus scrubs, mochila y cuaderno la presentan como estudiante de un programa de salud entre clases. La asesora académica fue creada como una persona nueva y visualmente distinta de la referencia.

Esta regla se mantiene para todas las futuras imágenes: los modelos de esa carpeta representan estudiantes o aspirantes; Sabi puede asumir funciones más amplias cuando el concepto lo requiera.

## Referencia utilizada

- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_49_50 AM - Copy (1).png`

## Prompt

```text
Use case: photorealistic-natural
Asset type: horizontal 16:9 website hero image for the SABER College Academic Affairs page.
Primary request: create a completely new candid academic-advising scene using the supplied image only as the identity and appearance reference for one adult SABER College student. Show the Black adult student with short natural twists inspired by Image 1 meeting with a distinct, newly generated female academic advisor in a bright contemporary college advising area. They stand beside a clean wall-mounted academic planning board made of simple colored blocks and timeline shapes with no readable text. The student holds a navy notebook and points naturally toward one part of the course sequence while the advisor listens and guides her with a blank tablet. The image should communicate personalized academic advisement, understanding program requirements, planning course sequence, monitoring progress, early support, tutoring access, and staying on track from enrollment through graduation without promising a particular academic result.
Input images: Image 1 is the identity, facial appearance, skin tone, and short natural twists reference for the adult student only. She must appear exclusively as a current student, never as an advisor, administrator, faculty member, healthcare professional, or staff member. The academic advisor must be a clearly distinct newly generated woman, approximately middle-aged, with no facial resemblance to the reference student.
Scene/backdrop: luminous contemporary Miami college academic advising or student support area with soft white and warm-ivory architecture, blue-tinted glass, a clean planning wall, large windows, restrained greenery, and natural daylight; organized, supportive, and clearly educational rather than corporate or clinical.
Subject: exactly two foreground adults. The reference student wears clean royal-blue student scrubs with a compact navy backpack and carries a simple notebook, clearly presented as a healthcare student between classes. The distinct academic advisor wears a navy blazer over a warm-ivory blouse with tailored neutral trousers and holds an unbranded tablet. Their body language is collaborative, respectful, and focused.
Style/medium: premium realistic editorial campus photography, candid and human, natural skin texture, credible anatomy and proportions, authentic fabric folds, polished without looking like staged corporate stock photography.
Composition/framing: exact 16:9 landscape composition at eye level, medium-wide three-quarter body framing, both people and the central portion of the planning board grouped near the center with comfortable breathing room; safe for responsive cropping inside the existing right-side hero image panel; both faces and all hands fully visible; board, tablet, notebook, and backpack visible but secondary.
Lighting/mood: soft Miami morning daylight, encouraging, calm, organized, focused, optimistic, and trustworthy.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with restrained warm-yellow accents in the abstract planning blocks.
Constraints: the supplied reference model retains a student role only; the advisor is a new and visually distinct person; exactly two foreground adults; natural eye contact, gestures, hands, scale, shadows, and posture; all board elements, tablet screens, and notebook pages must be blank or abstract and unreadable; no text anywhere in the image; no visible personal student records or grades.
Avoid: readable text, letters, numbers, logos, watermarks, school names, badges, IDs, transcripts, grades, attendance records, signatures, diplomas, graduation caps, certificates, acceptance letters, medical procedures, patients, hospital settings, stethoscopes, staff scrubs, professor lecturing, handshakes, staged thumbs-up poses, exaggerated smiles, distressed expressions, distorted hands, duplicated people, extra limbs, cluttered offices, heavy blue color grading.
```

## Validación

- Ambas versiones conservan una relación exacta de 16:9.
- El modelo de referencia aparece exclusivamente como estudiante.
- La asesora es una persona nueva y claramente distinta.
- Los scrubs están asociados a una estudiante con mochila y cuaderno, sin pacientes ni procedimientos clínicos.
- El tablero contiene únicamente bloques y líneas abstractas; no muestra texto, nombres, calificaciones o registros.
- Se revisaron rostros, manos, proporciones, tableta, cuaderno y mochila.
- No se modificó ni se importó contenido en WordPress.

## SHA-256

- Master: `1829BFFDFF610D6636ABBDA7A3880294A804A0663D6DED0F4201461E6F06B323`
- Web: `8C20D36DF2CA25F8494EF2CF30DE837314FFADB93EBC2B7CCDB4BC775644782B`

---

## Imagen secundaria — Student-centered support

Recurso creado para sustituir la fotografía exterior del campus en el bloque final de la página, junto al mensaje sobre un entorno de apoyo centrado en el estudiante.

### Archivos

- `academic-affairs-support-campus-master.png` — 2048 × 1152 px, relación 16:9.
- `academic-affairs-support-campus-web.png` — 1600 × 900 px, relación 16:9, versión optimizada para web.
- Artefacto original de generación: `output/imagegen/academic-affairs-support-campus-v1.png`.

### Concepto

Dos estudiantes adultas colaboran en una mesa de estudio dentro de un academic commons luminoso. Una toma notas y la otra consulta un portátil. La escena traduce el mensaje del bloque en una experiencia humana de apoyo entre pares, motivación, inclusión y preparación para avanzar hacia metas educativas y profesionales.

Ambas personas tomadas de `vistas-modelos` conservan exclusivamente el rol de estudiantes. No aparece personal académico ni administrativo.

### Referencias utilizadas

- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_49_50 AM - Copy (1).png`
- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 09_55_23 AM - Copy.png`

### Prompt

```text
Use case: photorealistic-natural
Asset type: horizontal 16:9 supporting section image for the closing campus block on the SABER College Academic Affairs page.
Primary request: create a completely new candid student-support scene using the supplied images only as identity and appearance references for two adult SABER College students. Show the Black student with short natural twists inspired by Image 1 and the Latina student with long dark wavy hair inspired by Image 2 studying together in a bright contemporary college academic commons. The Black student, wearing clean royal-blue student scrubs and a compact navy backpack placed beside her chair, is seated with an open notebook. The Latina student, wearing polished student smart-casual navy and warm-ivory clothing, sits beside her and helps compare notes on an unbranded laptop. Their natural peer collaboration should communicate a supportive, student-centered environment where learners feel respected, motivated, included, and equipped to work toward their educational and career goals, without implying guaranteed academic or career outcomes.
Input images: Image 1 is the identity, facial appearance, skin tone, and short natural twists reference for the Black adult student. Image 2 is the identity, facial appearance, skin tone, and long dark wavy hair reference for the Latina adult student. Both referenced people must appear exclusively as current students, never as advisors, faculty, administrators, staff, tutors employed by the college, or healthcare professionals.
Scene/backdrop: luminous contemporary Miami college academic commons or study lounge with soft white and warm-ivory architecture, blue-tinted glass, large windows, restrained greenery, comfortable student seating, and natural daylight; welcoming, active, and clearly educational rather than corporate or clinical.
Subject: exactly two foreground adult students seated side by side at a clean study table. One has a notebook and pen; the other uses a blank unbranded laptop. A simple closed textbook and the student's backpack may be visible. Their posture and expressions are focused, warm, and collaborative rather than posed.
Style/medium: premium realistic editorial campus photography, candid and human, natural skin texture, credible anatomy and proportions, authentic fabric folds, polished without looking like staged stock photography.
Composition/framing: exact 16:9 landscape composition at eye level, medium-wide seated framing, both students grouped near the center with comfortable breathing room and balanced architectural depth; safe for responsive cropping inside the existing rounded landscape image frame; both faces and all hands fully visible; laptop, notebook, pen, book, and backpack visible but secondary.
Lighting/mood: soft Miami morning daylight, supportive, calm, inclusive, motivated, optimistic, and trustworthy.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with one restrained warm-yellow environmental accent.
Constraints: both supplied reference models retain student roles only; exactly two foreground adults; natural eye contact, gestures, hands, scale, shadows, and posture; all laptop screens, notebook pages, and books must be blank or unreadable; no text anywhere in the image; no visible personal student records, grades, or private data.
Avoid: readable text, letters, numbers, logos, watermarks, school names, badges, IDs, transcripts, grades, attendance records, signatures, diplomas, graduation caps, certificates, acceptance letters, medical procedures, patients, hospital settings, stethoscopes, staff uniforms, professor or administrator roles, employee tutoring, handshakes, staged thumbs-up poses, exaggerated smiles, distressed expressions, distorted hands, duplicated people, extra limbs, cluttered tables, heavy blue color grading.
```

### Validación

- La composición se mantiene legible dentro del marco horizontal mostrado en la captura de referencia.
- Ambas modelos aparecen exclusivamente como estudiantes colaborando entre pares.
- No aparecen asesores, docentes, administradores ni personal institucional.
- El portátil, el cuaderno y el libro no muestran texto o datos legibles.
- No se representan resultados académicos o profesionales garantizados.
- No se modificó ni se importó contenido en WordPress.

### SHA-256

- Master: `B0BE9A51D563D0297CA7ED81F73072099CEE607A45B2C4ACCAC0D8057D29501D`
- Web: `11C15ED519EBCE1205B1C76E7EFC4690F4AF9FACB30C0A28E77CFABDA10649C5`
