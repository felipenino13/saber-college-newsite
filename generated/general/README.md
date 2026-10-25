# General Overview hero

Fecha: 2026-10-07  
Pagina objetivo: `/general/`  
Estado: generado y validado localmente; no importado a WordPress

## Archivos finales

- `general-overview-hero-master.png`: fuente final de 1536 x 1024 px.
- `general-overview-hero-web.png`: copia optimizada de 1200 x 800 px.
- Artefacto original: `output/imagegen/general-overview-hero-v1.png`.

La subcarpeta `visa-i-20-assistance/` corresponde a otra pagina y no fue
modificada durante esta generacion.

## Generacion

- Modelo: `gpt-image-2` mediante la API de OpenAI y el CLI oficial de la
  habilidad Image Generation.
- Calidad: `high`.
- Formato: PNG.
- Caso de uso: `photorealistic-natural`.
- La credencial se cargo localmente desde `.env.local` y no se guarda en esta
  carpeta ni en la documentacion.

## Concepto

La pagina funciona como puerta de entrada a programas, tuition, recursos,
politicas, vida de campus y preparacion profesional. Por eso el hero presenta
una bienvenida de orientacion general: dos estudiantes adultas recorren el
campus junto a Sabi. La escena comunica comunidad, confianza y un camino claro
para comenzar.

Sabi se utiliza aqui porque la direccion de marca lo reserva para comunidad,
bienvenida y orientacion. No reemplaza los sellos ni la comunicacion formal de
acreditacion.

## Referencias utilizadas

1. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 09_55_23 AM - Copy.png`
2. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_49_50 AM - Copy (1).png`
3. `src/img-referencias/vistas-modelos/ChatGPT Image Aug 12, 2026, 01_36_45 PM - copia.png`

Las referencias se usaron para la apariencia de las estudiantes, los uniformes
y el personaje de Sabi. La escena, el campus, las poses y la composicion son
nuevos.

## Prompt final

```text
Use case: photorealistic-natural
Asset type: horizontal 3:2 website hero image for the SABER College General Overview page.
Primary request: create a completely new, welcoming campus-orientation scene using the supplied images only as visual references. Show the Latina adult student inspired by Image 1 and the Black adult student with short natural twists inspired by Image 2 arriving together in a bright contemporary Miami college atrium, accompanied by Sabi, the friendly SABER tiger mascot inspired by Image 3. The students wear clean royal-blue healthcare scrubs and carry simple, unbranded academic materials: one slim notebook and one tablet. Sabi gives a natural open-hand welcome gesture while walking with them. The image should communicate a clear path forward, belonging, reliable information, academic opportunity, campus resources, and confidence about beginning college.
Input images: Image 1 is the appearance and scrub reference for the Latina adult student. Image 2 is the appearance and scrub reference for the Black adult student with short natural twists. Image 3 is the visual reference for Sabi's friendly tiger character, navy outfit, orange fur, and approachable proportions; use a single Sabi only.
Scene/backdrop: luminous modern Miami college atrium with soft white and warm-ivory architecture, large windows, restrained greenery, subtle glass, and bright natural daylight; no readable signs or institutional text.
Style/medium: premium realistic editorial campus photography with a polished, believable life-size mascot character integrated naturally into the scene; natural skin texture, credible human anatomy, authentic fabric folds, refined but not artificial.
Composition/framing: wide horizontal 3:2 composition, eye-level camera, three-quarter body framing, the three subjects walking comfortably together and occupying the center-right area with balanced breathing room; suitable for responsive cropping inside the existing right-side hero image panel; faces and hands fully visible.
Lighting/mood: soft Miami morning light, welcoming, optimistic, energetic but composed, trustworthy and inclusive.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with controlled warm-yellow accents and Sabi's natural orange fur.
Constraints: exactly two adult women and one Sabi mascot; preserve the distinguishing appearance and hairstyles of both referenced student models; both students wear royal-blue healthcare scrubs without any logo; Sabi wears a solid SABER-navy athletic hoodie and pants with restrained yellow trim and no text or logo; one mascot only; natural hands, walking poses, scale, shadows, and eye lines; no text in the image.
Avoid: readable text, logos, watermarks, badges, duplicated people or mascots, extra limbs, distorted hands, juvenile students, hospital setting, medical procedures, graduation caps, diplomas, giant flags, exaggerated cartoon effects, mascot costume seams, staged thumbs-up poses, cluttered background, heavy blue color grading.
```

## Validacion

- El concepto responde al mensaje de bienvenida y orientacion general de la
  pagina.
- Sabi aparece una sola vez y se integra de forma natural con la escena.
- Las estudiantes conservan rasgos, peinados y uniformes coherentes con las
  referencias.
- Las figuras principales tienen anatomia, manos, escala y sombras naturales.
- No contiene texto, logos, marcas de agua ni documentos legibles.
- La composicion admite recorte responsive dentro del panel derecho existente.
- No se modifico Elementor, la biblioteca multimedia ni ningun archivo de
  WordPress.

## Integridad

- Master SHA-256: `397E2F252AE106BD5D0437366A48A11163DE65E9F50806DA2ACE03EF1DF87D9A`
- Web SHA-256: `A0C739974011804EE7616933BED475EE2D2FBF4BE3B9D5E3354FD02EE1E76AB1`
