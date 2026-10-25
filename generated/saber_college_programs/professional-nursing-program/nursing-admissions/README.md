# Nursing Admissions hero

Fecha: 2026-10-07  
Pagina objetivo: `/saber_college_programs/professional-nursing-program/nursing-admissions/`  
Estado: generado y validado localmente; no importado a WordPress

## Archivos finales

- `nursing-admissions-hero-master.png`: fuente final de 2048 x 1152 px.
- `nursing-admissions-hero-web.png`: copia optimizada de 1600 x 900 px.
- Artefacto original: `output/imagegen/nursing-admissions-hero-v1.png`.

Todos los entregables usan la relacion de aspecto **16:9** solicitada para los
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

La pagina explica requisitos de admision, entrevista, orientacion, asesoria
financiera, apoyo personal y servicios de colocacion. El hero representa una
consulta concreta entre una aspirante, una asesora de admisiones y una
estudiante actual de Nursing. La escena comunica claridad y acompanamiento sin
sugerir aceptacion, ayuda financiera o empleo garantizados.

## Referencias utilizadas

1. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 09_55_23 AM - Copy.png`
2. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_49_50 AM - Copy (1).png`

La primera referencia se uso para la apariencia de la aspirante y la segunda
para la estudiante de Nursing y su uniforme azul. La asesora, la oficina, las
poses y la composicion son nuevos.

## Prompt final

```text
Use case: photorealistic-natural
Asset type: horizontal 16:9 website hero image for the SABER College Professional Nursing Program Nursing Admissions page.
Primary request: create a completely new, candid nursing-admissions consultation scene using the supplied images only as visual references for two adult student models. Show a prospective nursing applicant inspired by Image 1 seated at a clean admissions table with a friendly female admissions advisor and a current Professional Nursing student ambassador inspired by Image 2. The advisor calmly explains the admissions pathway using a slim tablet and a simple blank checklist folder. The applicant listens attentively and holds a pen, while the current nursing student offers a reassuring, experienced presence. The image should communicate clear requirements, personal guidance, preparation, informed decision-making, financial-aid support, and a realistic path into the nursing program without implying guaranteed admission.
Input images: Image 1 is the appearance and hair reference for the Latina adult prospective applicant; change her clothing to polished smart-casual navy and warm-ivory attire. Image 2 is the appearance, short natural twists, and royal-blue scrub reference for the Black adult current nursing student ambassador.
Scene/backdrop: bright contemporary Miami college admissions office or student consultation area with soft white and warm-ivory architecture, subtle glass, restrained greenery, and natural daylight; professional and welcoming, not a hospital or clinical room.
Style/medium: premium realistic editorial photography, candid and human, natural skin texture, credible anatomy and proportions, authentic fabric folds, polished without looking like staged stock photography.
Composition/framing: exact 16:9 landscape composition at eye level, medium-wide framing from seated waist upward, all three women clearly visible around one side of a clean table, balanced for responsive cropping inside the existing right-side hero image panel; faces, hands, tablet, folder, and pen fully visible without crowding.
Lighting/mood: soft Miami morning light, calm, encouraging, focused, trustworthy and professional.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with one restrained warm-yellow environmental accent.
Constraints: exactly three foreground adult women; one prospective applicant in smart-casual clothing, one admissions advisor in a warm-ivory blazer with navy accents, and one current nursing student in clean royal-blue healthcare scrubs without a logo; preserve the distinguishing appearance and hairstyles of both referenced models; natural eye contact and hand positions; all papers and screens blank or unreadable; no text in the image.
Avoid: readable text, logos, watermarks, badges, acceptance letters, diplomas, graduation caps, medical procedures, hospital equipment, stethoscopes, giant flags, handshakes, staged thumbs-up poses, exaggerated smiles, distorted hands, duplicated people, extra limbs, cluttered desk, heavy blue color grading.
```

## Validacion

- La composicion master y la version web son 16:9 exacto.
- La escena representa admision y orientacion al programa, no actividad clinica.
- Los tres roles se distinguen por vestuario, postura y contexto.
- No comunica aceptacion, ayuda financiera ni empleo garantizados.
- Las figuras tienen anatomia, manos y proporciones naturales.
- Los documentos y la pantalla no contienen texto legible.
- No contiene logos, marcas de agua, badges ni sellos inventados.
- No se modifico Elementor, la biblioteca multimedia ni ningun archivo de
  WordPress.

## Integridad

- Master SHA-256: `26D0A205930821D03303028C4E5D3440904BBF347A129AFBD168ABEB964FA9AA`
- Web SHA-256: `306149CBD5673423C958B380E4344BC73EC7EE44F3CEB831E1B9256A92B3AC07`
