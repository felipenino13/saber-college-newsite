# Visa I-20 Assistance

Pagina: `/general/visa-i-20-assistance/`  
Fecha: `2026-10-06`  
Estado: integrada y validada  
Modelo: `gpt-image-2` mediante CLI/API  
Calidad: `high`  
Use case: `photorealistic-natural`

## Archivos finales

| Archivo | Dimensiones | Uso | SHA-256 |
| --- | ---: | --- | --- |
| `visa-i20-students-master.png` | 1536 x 1024 | Fuente maestra | `1ED165398B7CA688852E155D5314C7DAF2EF71A294AA7ADB18F5AC2C92ADF828` |
| `visa-i20-students-web.png` | 1200 x 800 | Importacion en WordPress | `B091A33A7F63874B9DFC57E08C02058D5D6269AC0988C25798D45A94315A93B6` |

## Integracion en WordPress

- Pagina Elementor: `VISA I-20 Assistance`
- Post ID: `898`
- Attachment ID: `2241`
- Archivo publicado: `/wp-content/uploads/2026/10/saber-college-international-students-visa-i20.png`
- Alt text: `International students walking together at SABER College in Miami`
- Respaldo Elementor: `/tmp/visa-i20-elementor-before-image-20261006-193150.json`
- Script de integracion: `scripts/replace-visa-i20-image.php`

## Referencias visuales

- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 09_55_23 AM - Copy.png`
- `src/img-referencias/vistas-modelos/ChatGPT Image Jun 22, 2026, 11_49_50 AM - Copy (1).png`

Las referencias se utilizaron para mantener consistencia en diversidad,
apariencia adulta, uniforme azul y calidad fotografica. No se usaron como
contenido literal ni como una hoja de poses en la imagen final.

## Prompt principal

Create a premium photorealistic 3:2 editorial feature image for the SABER
College Visa I-20 Assistance page. Show three diverse adult international
college students walking together in a natural candid conversation on a bright
contemporary Miami campus. Two women wear clean royal-blue healthcare scrubs
without logos, and one male student wears smart casual navy and cream clothing.
Use natural skin texture, credible proportions, soft Miami morning daylight,
airy white and warm-ivory architecture, pale blue, SABER navy and royal blue,
and one restrained warm-yellow accent. Avoid readable text, logos, watermarks,
passports, immigration documents, giant flags, thumbs-up poses, stock-photo
cutouts, stereotypical tourist imagery, and medical procedures.

## Correccion final

Se conservo la primera escena y se cambio solamente la estudiante izquierda
por una mujer afrodescendiente adulta con piel marron profunda y cabello natural
corto, inspirada en la segunda referencia. Se preservaron composicion, postura,
uniforme, iluminacion, campus y los otros dos estudiantes.

## Validacion

- La imagen anterior ya no aparece en el JSON de Elementor.
- El JSON de Elementor es valido.
- WordPress genero variantes de 150, 300, 768 y 1024 pixeles.
- La pagina responde HTTP 200 y utiliza la nueva imagen responsive.
- No hay desbordamiento horizontal en un viewport de 390 pixeles.
- No se modificaron los textos de la pagina.

## Documentacion relacionada

- `reports/visa-i20-image-20261006.md`
- `scripts/replace-visa-i20-image.php`
