# Checklist de indexación para IA — SABER College

**Entorno trabajado:** WordPress local (`http://localhost:8082`)  
**Fecha de ejecución:** 21 de octubre de 2026  
**Producción:** sin cambios

## Alcance acordado

- [x] Ejecutar localmente las mejoras de rastreo, entidad, schema, metadatos, `llms.txt`, idiomas, enlaces oficiales y accesibilidad de imágenes.
- [x] Mantener Elementor editable y conservar el texto visible de las páginas diseñadas con Elementor.
- [x] No cambiar `blog_public`, `index`, `noindex` ni la política de robots de local o staging, por decisión expresa del usuario.
- [x] No publicar ni modificar `https://sabercollege.edu`.

## 1. Línea base y respaldo

- [x] Exportar la base de datos completa antes de los cambios.
- [x] Guardar las opciones de Rank Math antes de los cambios.
- [x] Guardar el MU plugin anterior.
- [x] Exportar `_elementor_data` de la página de acreditación antes de editar enlaces.
- [x] Rastrear sitemap, códigos HTTP, títulos, descripciones, canonical, `lang`, H1, JSON-LD, enlaces e imágenes.
- [x] Guardar `robots.txt`, sitemap y `llms.txt` iniciales.

**Evidencia:** `reports/backups/ai-indexation-20261021/` y `reports/ai-indexation-before-20261021/`.

## 2. Política de indexación

- [x] **Excluido por decisión del usuario:** configurar local o staging como `noindex`.
- [x] Confirmar que `blog_public` permaneció en `1` antes y después.
- [x] Confirmar que las opciones de robots de Rank Math no cambiaron.
- [x] Mantener dinámicos los canonical, schema, sitemap y `llms.txt` mediante la URL configurada en WordPress.
- [x] Confirmar que no hay referencias al dominio temporal `abc16946.sg-host.com` en la base de datos local.

**Evidencia:** `reports/ai-indexation-after-20261021/noindex-safeguard.json`.

## 3. Entidad institucional y schema

- [x] Cambiar la entidad global de `Organization` a `CollegeOrUniversity`.
- [x] Mantener un `@id` institucional estable y dinámico: `home_url('/#organization')`.
- [x] Añadir nombre legal, nombre público, año de fundación, dirección, teléfono, correo y perfiles sociales oficiales.
- [x] Añadir los identificadores CIE 1400 y COE 220500 con enlaces a sus directorios oficiales.
- [x] Añadir `EducationalOccupationalProgram` a Professional Nursing.
- [x] Añadir `EducationalOccupationalProgram` a Physical Therapist Assistant.
- [x] Conectar cada programa con la institución y su `WebPage`.
- [x] Añadir un `ItemList` de programas a `/saber_college_programs/`.
- [x] Añadir citas de fuentes primarias a la página de acreditación.
- [x] Identificar a SABER College como autor institucional de los artículos.
- [x] Validar sintaxis JSON-LD y campos institucionales/programáticos en local.

## 4. Metadatos, encabezados e imágenes

- [x] Corregir dos meta descriptions que producían JSON-LD inválido.
- [x] Añadir meta descriptions únicas a las ocho categorías activas.
- [x] Cambiar a H2 el H1 duplicado dentro del contenido de 23 artículos; el título del tema permanece como único H1.
- [x] Completar el `alt` de cuatro imágenes institucionales compartidas.
- [x] Completar el `alt` de las imágenes destacadas de los artículos cuando estaba vacío.
- [x] Corregir el `alt` vacío de una imagen de testimonio dentro de un artículo.
- [x] Confirmar cero meta descriptions vacías, cero páginas sin canonical, cero páginas con H1 incorrecto y cero imágenes sin `alt` en el sitemap actual.

## 5. `llms.txt` y señales de idioma

- [x] Limitar `llms.txt` a páginas institucionales útiles.
- [x] Usar las meta descriptions aprobadas como resúmenes.
- [x] Eliminar artículos, tipos utilitarios y taxonomías del archivo.
- [x] Eliminar caracteres `???` y extractos genéricos como `Home -`.
- [x] Añadir datos institucionales y fuentes oficiales de verificación.
- [x] Configurar `lang="es-US"` en los 12 artículos en español confirmados.
- [x] Añadir `hreflang` `en-US`, `es-US` y `x-default` a las 12 parejas bilingües.
- [x] Validar las 24 URLs de las parejas bilingües.

## 6. Autoridad y navegación

- [x] Enlazar los logotipos CIE, COE, CAPTE y NLN CNEA de la página de acreditación con sus fuentes primarias.
- [x] Añadir nombre accesible a cada enlace de acreditación y abrir la fuente en una pestaña nueva.
- [x] Conservar los enlaces internos, breadcrumbs, navegación y footer existentes entre About, Programs, Admissions, Tuition, Accreditation y Student Services.
- [x] Conservar las introducciones visibles existentes; no fue necesario agregar texto nuevo.
- [x] Mantener la fecha de modificación y la responsabilidad institucional mediante el schema generado.

## 7. Validación posterior

- [x] Regenerar el sitemap de Rank Math.
- [x] Retirar siete URLs antiguas que devolvían 404 de la caché del sitemap.
- [x] Rastrear las 125 URLs finales: todas responden HTTP 200.
- [x] Validar cero bloques JSON-LD inválidos.
- [x] Validar una sola entidad `CollegeOrUniversity` por página.
- [x] Validar ambos `EducationalOccupationalProgram`.
- [x] Validar que `llms.txt` contiene 26 páginas curadas.
- [x] Confirmar HTTP 200 con Googlebot, GPTBot, OAI-SearchBot, ChatGPT-User y ClaudeBot.
- [x] Comparar Elementor antes/después: 54 nodos, 24 widgets y texto visible idéntico.
- [x] Revisar la página de acreditación en escritorio y móvil: un H1, sin overflow, sin errores de consola y cuatro enlaces oficiales correctos.
- [x] Ejecutar dos veces el script de aplicación y confirmar que la segunda ejecución no hace cambios.

**Evidencia:** `reports/ai-indexation-after-20261021/validation.json`, `browser-qa.json`, `inventory.json`, `accreditation-desktop.png` y `accreditation-mobile.png`.

## 8. Pendiente para el lanzamiento aprobado

- [ ] Crear un respaldo final del WordPress aprobado.
- [ ] Migrar las URLs al dominio `https://sabercollege.edu`.
- [ ] Confirmar canonical, sitemap, schema y `llms.txt` con el dominio público.
- [ ] Configurar redirecciones 301 desde cualquier URL temporal que haya sido compartida o indexada.
- [ ] Validar schema con una herramienta pública una vez que el sitio sea accesible en el dominio final.
- [ ] Repetir el rastreo y la prueba visual en producción.
- [ ] Enviar el sitemap a Google Search Console y Bing Webmaster Tools.
- [ ] Revisar logs de rastreo y cobertura durante las primeras semanas.

## Criterio de cierre local

- [x] No se cambió la política de indexación solicitada por el usuario.
- [x] Todas las URLs del sitemap local responden 200 y tienen metadatos y canonical.
- [x] SABER College y sus dos programas tienen entidades válidas y conectadas.
- [x] `llms.txt` contiene únicamente páginas institucionales seleccionadas.
- [x] Elementor continúa editable y el texto visible permanece intacto.
- [x] La validación automatizada local terminó con `passed: true`.
