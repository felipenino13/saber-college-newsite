# PTA Admissions hero

Fecha: 2026-10-07  
Pagina objetivo: `/saber_college_programs/physical-therapist-assistant-program/admissions-pta/`  
Estado: generado y validado localmente; no importado a WordPress

## Archivos finales

- `pta-admissions-hero-master.png`: fuente final de 2048 x 1152 px.
- `pta-admissions-hero-web.png`: copia optimizada de 1600 x 900 px.
- Artefacto original: `output/imagegen/pta-admissions-hero-v1.png`.

Todos los entregables usan la relacion de aspecto **16:9** definida para los
nuevos heroes del proyecto.

## Generacion

- Modelo: `gpt-image-2` mediante la API de OpenAI y el CLI oficial de la
  habilidad Image Generation.
- Calidad: `high`.
- Formato: PNG.
- Caso de uso: `photorealistic-natural`.
- La credencial se cargo localmente desde `.env.local` y no se guarda en esta
  carpeta ni en la documentacion.

## Concepto

La pagina destaca requisitos academicos, entrevista, cartas de recomendacion,
horas de observacion, preparacion clinica y cupos limitados. El hero muestra una
orientacion dentro de un laboratorio PTA entre una aspirante, un coordinador del
programa y una estudiante actual. El entorno permite reconocer la disciplina
sin convertir la escena en una consulta o procedimiento clinico.

La imagen comunica preparacion e interes informado. No sugiere que cumplir los
requisitos garantice la aceptacion.

## Referencias utilizadas

1. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_49_50 AM - Copy (1).png`
2. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_03_24 AM - Copy (1).png`

La primera referencia se uso para la apariencia de la aspirante y la segunda
para la estudiante PTA y su uniforme azul. El coordinador, el laboratorio, las
poses y la composicion son nuevos.

## Prompt final

```text
Use case: photorealistic-natural
Asset type: horizontal 16:9 website hero image for the SABER College Physical Therapist Assistant Program Admissions page.
Primary request: create a completely new, candid PTA admissions orientation scene using the supplied images only as visual references for two adult student models. Show a prospective PTA applicant inspired by Image 1 visiting a bright Physical Therapist Assistant teaching lab with a professional male program advisor and a current PTA student ambassador inspired by Image 2. The advisor explains the admissions pathway and observation-hour expectations using a slim tablet while the applicant holds a simple navy portfolio. The current student stands nearby in royal-blue healthcare scrubs and contributes naturally to the conversation. The scene should communicate preparation, observation experience, professionalism, communication skills, clinical readiness, a competitive admissions process, and an informed commitment to the physical therapy profession without implying guaranteed acceptance.
Input images: Image 1 is the appearance and short natural twists reference for the Black adult prospective applicant; change her clothing to polished smart-casual navy and warm-ivory attire. Image 2 is the appearance, long brown hair, and royal-blue scrub reference for the adult current PTA student ambassador.
Scene/backdrop: contemporary Miami college PTA skills lab with clean white and warm-ivory architecture, large windows, subtle glass, a neatly made padded therapy table, parallel bars or balance-training equipment softly visible in the background, and restrained greenery; educational and realistic, not a hospital treatment room.
Style/medium: premium realistic editorial photography, candid and human, natural skin texture, credible anatomy and proportions, authentic fabric folds, polished without looking like staged stock photography.
Composition/framing: exact 16:9 landscape composition at eye level, medium-wide three-quarter body framing, the three adults standing in a relaxed conversational triangle with balanced breathing room; suitable for responsive cropping inside the existing right-side hero image panel; faces, hands, tablet, and portfolio fully visible.
Lighting/mood: soft Miami morning light, focused, welcoming, capable, optimistic and trustworthy.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with one restrained warm-yellow environmental accent.
Constraints: exactly three foreground adults; one prospective applicant in smart-casual attire, one male PTA program advisor in a navy polo with warm-ivory trousers and no logo, and one current PTA student in clean royal-blue healthcare scrubs without a logo; preserve the distinguishing appearance and hairstyles of both referenced models; natural eye contact, hand positions, scale and shadows; all papers and screens blank or unreadable; no text in the image; therapy equipment remains secondary and correctly proportioned.
Avoid: readable text, logos, watermarks, badges, acceptance letters, diplomas, graduation caps, active patient treatment, pain expressions, hospital beds, wheelchairs as decorative props, stethoscopes, giant flags, handshakes, staged thumbs-up poses, exaggerated smiles, distorted hands, duplicated people, extra limbs, cluttered lab, heavy blue color grading.
```

## Validacion

- La composicion master y la version web son 16:9 exacto.
- La escena representa orientacion y preparacion para PTA, no tratamiento a un
  paciente.
- El laboratorio incluye camilla y barras paralelas con escala y ubicacion
  creibles.
- Los tres roles se distinguen por vestuario, postura y contexto.
- No comunica admision garantizada ni elimina el caracter competitivo del
  proceso.
- Las figuras tienen anatomia, manos y proporciones naturales.
- La pantalla y el portafolio no contienen texto legible.
- No contiene logos, marcas de agua, badges ni sellos inventados.
- No se modifico Elementor, la biblioteca multimedia ni ningun archivo de
  WordPress.

## Integridad

- Master SHA-256: `AC51B203CCDB4CF743BA517C9085DF4382832DAA1BC1530DAFBF1943718AF897`
- Web SHA-256: `761B9FF274170174EFCE27BB3936F0C10B80465E56176EA35D46619497080E49`
