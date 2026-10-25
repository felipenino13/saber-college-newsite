# Student Services hero

Fecha: 2026-10-07  
Pagina objetivo: `/student-services/`  
Estado: generado y validado localmente; no importado a WordPress

## Archivos finales

- `student-services-hero-master.png`: fuente final de 1536 x 1024 px.
- `student-services-hero-web.png`: copia optimizada de 1200 x 800 px.
- Artefacto original: `output/imagegen/student-services-hero-v1.png`.

## Generacion

- Modelo: `gpt-image-2` mediante la API de OpenAI y el CLI oficial de la
  habilidad Image Generation.
- Calidad: `high`.
- Formato: PNG.
- Caso de uso: `photorealistic-natural`.
- La credencial se cargo localmente desde `.env.local` y no se guarda en esta
  carpeta ni en la documentacion.

## Referencias utilizadas

Las referencias se usaron exclusivamente para conservar la apariencia de dos
modelos adultas, la diversidad y el color/ajuste de los uniformes azules. La
escena, la asesora, la composicion y el entorno son nuevos.

1. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 09_55_23 AM - Copy.png`
2. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_49_50 AM - Copy (1).png`
3. `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_03_13 AM - Copy.png`

No se utilizaron las referencias de Sabi porque el contenido de Student
Services se beneficia mas de una escena humana de orientacion y acompanamiento.

## Prompt final

```text
Use case: photorealistic-natural
Asset type: horizontal 3:2 website hero image for the SABER College Student Services page.
Primary request: create a completely new, candid campus support scene using the supplied images only as visual references for the two adult student models and the royal-blue healthcare scrubs. Show the Latina student inspired by Image 1 and the Black student with short natural twists inspired by Image 2 walking with a friendly female academic advisor through a bright contemporary Miami college student-services area. The advisor wears polished smart-casual navy and warm-ivory clothing. The three women are engaged in a natural, reassuring conversation while the advisor holds a slim tablet and one student carries a simple folder. The feeling should communicate guidance, belonging, accessible support, academic progress, career preparation, and student confidence.
Input images: Image 1 is the appearance and scrub reference for the Latina adult student. Image 2 is the appearance and scrub reference for the Black adult student with short natural twists. Image 3 is an additional reference for royal-blue scrub color, fit, and realistic fabric.
Scene/backdrop: luminous modern campus interior with soft white and warm-ivory architecture, subtle glass, restrained greenery, and hints of Miami daylight; no readable signage.
Style/medium: premium realistic editorial photography, candid and human, natural skin texture, credible anatomy and proportions, authentic fabric folds, no artificial stock-photo cutout look.
Composition/framing: wide horizontal 3:2 composition, eye-level camera, three-quarter body framing, the group comfortably centered with breathing room around them; suitable for responsive cropping inside the existing right-side hero image panel; no person cut off at the face or hands.
Lighting/mood: soft natural Miami morning light, welcoming, calm, optimistic, trustworthy.
Color palette: SABER navy, royal blue, white, warm ivory, pale blue, with one very restrained warm-yellow environmental accent.
Constraints: exactly three adult women; preserve the distinguishing appearance and hair of the two referenced student models; both students wear clean royal-blue healthcare scrubs without any logo; advisor is clearly distinguishable by smart-casual attire; natural hand positions and eye contact; no text in the image.
Avoid: logos, readable text, watermarks, badges, distorted hands, duplicated people, extra limbs, medical procedures, hospital setting, graduation caps, passports, immigration documents, giant flags, exaggerated smiles, staged thumbs-up poses, overly blurred background, heavy blue color grading.
```

## Validacion

- La escena representa acompanamiento academico y servicios estudiantiles, no
  un procedimiento clinico.
- Las tres personas tienen anatomia, manos y proporciones naturales.
- Las dos estudiantes conservan rasgos y peinados coherentes con las referencias.
- No contiene texto, logos, marcas de agua ni documentos legibles.
- La composicion permite recorte responsive en el panel derecho del hero actual.
- No se modifico Elementor, la biblioteca multimedia ni ningun archivo de
  WordPress.

## Integridad

- Master SHA-256: `9BB041AA422CB3F3F9958A196BE6B3E89B8E53742CCA2C23A92F56B627FB9BAB`
- Web SHA-256: `62636651C7AD73792B4487D1B602A06E02D087ACCAB56322A9ECA8EB9B4149D9`
