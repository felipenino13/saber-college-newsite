# Plan de Trabajo SEO para la Migración

Plan de acciones SEO para preparar la migración del WordPress local hacia `https://sabercollege.edu/`, actualizado el 28 de septiembre de 2026.

## Objetivo

Conservar la indexación, autoridad y tráfico del sitio público durante la migración, evitando cambios innecesarios de URL, contenido duplicado, enlaces rotos y bloqueos de rastreo.

## Estado general

| Área | Estado actual | Prioridad |
| --- | --- | --- |
| URLs de páginas | Comparadas y alineadas según `pages-compilado.md` | Resuelta / seguimiento |
| URLs de blogs existentes | 86 de 86 alineadas con producción | Resuelta |
| Blogs faltantes | 2 pendientes de migrar | Crítica |
| Documentos PDF | 200 importados localmente | Resuelta |
| Documentos Word | 3 de 3 importados y registrados localmente | Resuelta |
| Redirecciones conocidas | Configuradas y documentadas en Rank Math | Seguimiento |
| Auditoría completa de Rank Math | Metadatos completados; validación final en producción pendiente | Alta |
| Sitemap, robots e indexación | Pendiente de validación final | Alta |
| Rastreo técnico previo al lanzamiento | Pendiente | Crítica |

## Fase 1: Inventario y contenido pendiente

| Acción | Prioridad | Estado | Criterio de finalización |
| --- | --- | --- | --- |
| Migrar los 2 blogs públicos que todavía no existen en local | Crítica | Pendiente | Los dos artículos cargan localmente con la misma URL pública |
| Importar o reemplazar los 3 documentos Word pendientes | Alta | Completado | Los tres archivos conservan nombre, ruta histórica y registro multimedia local |
| Confirmar que imágenes, videos, PDF y fuentes estén alojados localmente | Alta | Pendiente | No quedan recursos esenciales cargados desde `sabercollege.edu` |
| Verificar que el contenido local corresponda con la intención de cada página pública | Alta | Pendiente | No existen páginas incorrectas, vacías o duplicadas |

## Fase 2: Rank Math por página y entrada

| Acción | Prioridad | Estado | Criterio de finalización |
| --- | --- | --- | --- |
| Revisar títulos SEO | Alta | Completado | Los 130 contenidos publicados tienen título SEO; ninguno supera 70 caracteres |
| Revisar meta descriptions | Alta | Completado | Los 130 contenidos publicados tienen una descripción de 70 a 160 caracteres |
| Revisar palabra clave principal | Media | Completado | Las 44 páginas y 86 entradas tienen una palabra clave coherente con su intención |
| Revisar canonical | Crítica | Validado en local; revisar tras migrar | Rank Math genera canonical automático y no se guardaron URLs de localhost en la base de datos |
| Revisar Open Graph y Twitter cards | Media | Completado | Los 130 contenidos tienen título y descripción social; 85 entradas usan su imagen destacada |
| Revisar schema | Alta | Completado | Las páginas indexables usan `WebPage`, las entradas `BlogPosting` y los contenidos `noindex` no fuerzan entidades globales |
| Revisar breadcrumbs | Media | Validado en muestras; rastreo final pendiente | Las rutas representativas no contienen URLs antiguas; se confirmarán en el rastreo integral |
| Revisar puntuación de Rank Math | Baja | Metadatos optimizados; recálculo editorial pendiente | Rank Math recalcula el número al abrir o guardar el editor; no se almacenaron puntuaciones artificiales |

### Resultado aplicado el 28 de septiembre de 2026

- Se auditaron y completaron 130 contenidos publicados: 44 páginas y 86 entradas.
- No faltan títulos SEO, meta descriptions, palabras clave, títulos sociales ni descripciones sociales.
- No existen títulos ni meta descriptions duplicados; todos los títulos tienen hasta 70 caracteres y las descripciones entre 70 y 160.
- Las 85 entradas con imagen destacada utilizan esa imagen en Facebook y Twitter. El resto conserva metadatos sociales de texto sin inventar imágenes.
- Las dos palabras clave repetidas corresponden a las páginas de Professional Nursing y PTA y a sus landings equivalentes; no compiten porque las landings permanecen en `noindex`.
- Se conservó la optimización personalizada existente del artículo sobre Sabi.
- Rank Math genera canonicals automáticos. No se guardaron canonicals absolutos de `localhost`, para que adopten el dominio definitivo al migrar.
- Ocho páginas generan `noindex, follow`: Plan Bunji, Thank You y las seis páginas del grupo `/lp/`.
- El schema queda como `WebPage` para páginas indexables y `BlogPosting` para entradas; la integración se conserva mediante un MU-plugin independiente del tema activo.

## Fase 3: Indexación

| Acción | Prioridad | Estado | Criterio de finalización |
| --- | --- | --- | --- |
| Confirmar páginas que deben ser `index` | Crítica | Completado | 36 páginas y 86 entradas generan `index, follow`, canonical y aparecen en el sitemap |
| Mantener `noindex` en `/lp/` y sus landings acordadas | Alta | Completado | Las seis páginas del grupo `/lp/` generan `noindex, follow` y no aparecen en el sitemap |
| Mantener `/plan-bunji/` como `noindex` hasta eliminarla | Media | Completado | Rank Math genera `noindex, follow` y la URL no aparece en el sitemap |
| Revisar páginas de conversión como `/thank-you/` | Alta | Completado | `/thank-you/` genera `noindex, follow` |
| Restaurar las categorías históricas del blog | Crítica | Completado | Diez categorías públicas y sus asignaciones se conservaron; se eliminó la categoría local `/category/blog/` |
| Controlar archivos de autor, fecha, búsqueda y etiquetas | Alta | Completado | Autor y fecha están desactivados; búsquedas y etiquetas permanecen en `noindex` y fuera del sitemap |
| Desactivar “Disuadir a los motores de búsqueda” al publicar | Crítica | Completado en local; revalidar al migrar | `blog_public=1`; WordPress no genera un `noindex` global |
| Revisar `robots.txt` | Crítica | Completado en local; revalidar dominio | Solo bloquea `/wp-admin/`, permite `admin-ajax.php` y declara el sitemap de Rank Math |

### Resultado aplicado el 28 de septiembre de 2026

- Se rastrearon los 130 contenidos publicados: 122 son indexables y 8 generan `noindex, follow`.
- Los 122 contenidos indexables responden `200`, incluyen canonical y aparecen en el sitemap correspondiente; no se detectaron inconsistencias.
- Los sitemaps contienen 86 entradas, 36 páginas y 10 categorías. Ninguna de las ocho páginas `noindex` aparece en ellos.
- Se restauraron las categorías Professional Nursing, Physical Therapist, Healthcare Program, Career Resources, Financial Aid, Higher Education, Medical Assisting, VESL y Sabi; `College News` reemplazó posteriormente a `Uncategorized` como categoría editorial activa.
- Las asignaciones se recuperaron para las 86 entradas locales que coinciden con producción. Los conteos inferiores en Professional Nursing y Healthcare Program corresponden a los dos blogs públicos todavía pendientes de migrar.
- Las 101 URLs históricas de etiquetas públicas no se recrearon porque son archivos de bajo valor y duplican agrupaciones; quedaron resueltas mediante redirecciones temáticas en la Fase 5.
- Los adjuntos redirigen a su contenido padre o, si son huérfanos, al inicio. El destino adoptará el dominio definitivo durante la migración.
- “More than a mascot…” quedó asignado a Sabi y “Venezuela Needs Us” a College News. `Uncategorized` quedó vacío y su URL histórica redirige `301` hacia `/saber_college_blog/`.

## Fase 4: Enlaces internos y navegación

| Acción | Prioridad | Estado | Criterio de finalización |
| --- | --- | --- | --- |
| Revisar menús principales y secundarios | Alta | Completado | Los 31 elementos del menú principal apuntan a páginas publicadas; Safety Plan y CARES Act quedaron bajo General |
| Revisar footer, botones y llamadas a la acción | Alta | Completado | Footer y CTA apuntan directamente a destinos válidos, sin `localhost` almacenado ni saltos intermedios |
| Revisar enlaces dentro de páginas y blogs | Alta | Completado | Los 99 destinos internos únicos responden directamente y no se detectaron enlaces `404` |
| Revisar enlaces hacia documentos | Alta | Completado | Los 29 documentos enlazados responden `200` desde la biblioteca local |
| Detectar páginas huérfanas | Media | Completado | No quedan contenidos indexables sin enlaces desde menú, contenido o archivos de categoría |
| Revisar textos ancla | Media | Completado | Los enlaces editoriales importantes describen su destino; `Apply Now` y `Request Info` se conservan como CTA claras |

### Resultado aplicado el 28 de septiembre de 2026

- Se rastrearon las 130 páginas y entradas publicadas y 99 destinos internos únicos.
- Se corrigieron cinco enlaces rotos y nueve enlaces que dependían de redirecciones antiguas.
- Los dos enlaces incorrectos de Inicio que apuntaban a `/nurse` ahora llevan a Professional Nursing y Physical Therapist Assistant, respectivamente.
- Los enlaces almacenados hacia `localhost:8082` o `sabercollege.edu` se convirtieron a rutas relativas; el conteo residual es cero.
- Safety Plan y CARES Act se agregaron como páginas dinámicas al submenú General para evitar que quedaran huérfanas.
- Los 29 enlaces a PDF y Word responden correctamente; no hay documentos rotos entre los recursos enlazados actualmente.
- El rastreo final reportó cero errores de origen, cero destinos rotos, cero saltos de redirección y cero contenidos indexables huérfanos.

## Fase 5: Redirecciones

| Acción | Prioridad | Estado | Criterio de finalización |
| --- | --- | --- | --- |
| Validar todas las reglas activas de Rank Math | Crítica | Completado | Los 111 orígenes devuelven `301` hacia el destino correcto |
| Eliminar redirecciones obsoletas | Alta | Completado | Ninguna regla intercepta una página o entrada que ahora existe |
| Detectar cadenas de redirección | Alta | Completado | Las 111 URLs antiguas llegan al destino final con un solo salto |
| Detectar bucles | Crítica | Completado | No se detectaron ciclos ni redirecciones hacia el mismo origen |
| Comprobar destinos eliminados o `404` | Crítica | Completado | Los 111 destinos responden `200` |
| Resolver las 101 etiquetas históricas del sitio público | Alta | Completado | Cada etiqueta redirige a una categoría temática o al archivo editorial general |
| Exportar las redirecciones antes de migrar | Alta | Completado | La configuración recuperable está en `redirections.csv` |

### Resultado de la Fase 5

- Rank Math contiene **111 redirecciones activas y exactas**: las 10 reglas editoriales existentes y 101 reglas nuevas para etiquetas históricas.
- La auditoría final obtuvo **111 respuestas `301`**, **111 destinos `200`**, cero duplicados, cero cadenas, cero bucles y cero contenidos vigentes interceptados.
- Las etiquetas se distribuyeron por relevancia: 57 a Professional Nursing, 17 a Physical Therapist, 7 a VESL, 2 a Medical Assisting, 6 a Career Resources, 1 a Healthcare Program y 11 al archivo general del blog.
- La redirección de `/want-to-study-nursing-but-work-during-the-week-discover-weekend-nursing/` es temporal. Debe eliminarse al importar esa entrada con su URL pública original.
- Las cuatro antiguas landings de programas continúan apuntando a sus equivalentes bajo `/lp/`, actualmente `noindex`, conforme a la decisión del proyecto.
- `redirections.csv` conserva el inventario completo con ID, origen, destino, código, estado y contador de visitas, usando rutas relativas para facilitar la migración.

## Fase 6: Sitemap y rastreo

| Acción | Prioridad | Estado | Criterio de finalización |
| --- | --- | --- | --- |
| Regenerar el sitemap XML de Rank Math | Crítica | Completado en local; repetir al migrar | El sitemap carga correctamente y adoptará el dominio definitivo al migrar |
| Comparar sitemap con inventarios de páginas y blogs | Alta | Completado para contenido local; 2 blogs públicos siguen pendientes de importar | Todos los contenidos locales indexables aparecen y ningún contenido `noindex` se filtró |
| Excluir adjuntos y archivos sin valor SEO | Media | Completado | Adjuntos, etiquetas, autores y tipos de contenido sin publicaciones no generan sitemaps |
| Ejecutar un rastreo completo del sitio local o staging | Crítica | Completado en local; repetir en staging | No hay `404`, enlaces rotos, canonicals incorrectos ni recursos ausentes |
| Revisar códigos HTTP | Alta | Completado | Las URLs indexables responden `200` y las antiguas usan `301` cuando corresponde |

### Resultado de la Fase 6

- Se invalidó el caché de sitemaps de Rank Math y se regeneraron las reglas de enlaces permanentes.
- `sitemap_index.xml` publica **3 sitemaps y 132 URLs**: 86 entradas, 36 páginas y 10 categorías.
- La comparación con `pages-new.md` y `blogs-new.md` es exacta: 36 de 44 páginas están indexadas, las 8 páginas `noindex` permanecen excluidas y las 86 entradas locales aparecen sin faltantes ni duplicados.
- El rastreo cubrió **152 URLs HTML** y **582 recursos internos**, incluidos los recursos descubiertos dentro de CSS. Todos respondieron `200` y no se detectaron enlaces internos redirigidos, `404`, canonicals incorrectos ni recursos ausentes.
- Las 132 URLs incluidas en sitemap tienen canonical autorreferente y directiva `index, follow`; las 8 exclusiones conservan `noindex`.
- `robots.txt` responde `200`, permite `admin-ajax.php`, bloquea `/wp-admin/` y declara correctamente `sitemap_index.xml`.
- No se publican sitemaps de etiquetas, autores, fechas ni adjuntos. Los archivos paginados encontrados durante el rastreo son accesibles pero no se agregan como entradas independientes al sitemap, que es el comportamiento esperado.
- Los reportes reproducibles están en `sitemap-urls.csv`, `crawl-report.csv` y `resource-report.csv`. El rastreo puede repetirse con `node phase-6-crawl.mjs`.
- Las URLs usan `localhost:8082` porque la verificación corresponde al entorno local. Tras migrar, se debe invalidar nuevamente el caché de Rank Math y repetir el rastreo sobre HTTPS y el dominio definitivo.

## Fase 7: SEO técnico y rendimiento

| Acción | Prioridad | Estado | Criterio de finalización |
| --- | --- | --- | --- |
| Validar HTTPS y contenido mixto | Crítica | Pendiente para producción | Todos los recursos cargan mediante HTTPS |
| Revisar versión móvil | Alta | Pendiente | Contenido, navegación y formularios funcionan en pantallas pequeñas |
| Revisar Core Web Vitals | Alta | Pendiente | No existen problemas graves de LCP, INP o CLS |
| Optimizar imágenes críticas | Alta | Pendiente | Tamaños, formatos, dimensiones y carga diferida son adecuados |
| Revisar caché y archivos de Elementor | Alta | Pendiente | CSS regenerado y diseño correcto sin archivos obsoletos |
| Revisar encabezados `H1` a `H6` | Media | Pendiente | Cada página tiene un `H1` principal y una jerarquía lógica |
| Revisar textos alternativos | Media | Pendiente | Las imágenes informativas tienen `alt` descriptivo |

## Fase 8: Preparación del lanzamiento

| Acción | Prioridad | Estado | Criterio de finalización |
| --- | --- | --- | --- |
| Crear copia de seguridad completa | Crítica | Pendiente | Base de datos, uploads, tema y configuración son recuperables |
| Probar la migración en staging | Crítica | Pendiente | El sitio funciona con el dominio y configuración de producción |
| Reemplazar URLs de localhost de forma segura | Crítica | Pendiente | No quedan referencias activas a `localhost:8082` |
| Regenerar enlaces permanentes y CSS de Elementor | Alta | Pendiente | Todas las rutas y estilos cargan correctamente |
| Validar Analytics y Tag Manager | Alta | Pendiente | Las etiquetas registran visitas y conversiones sin duplicarse |
| Preparar plan de reversión | Crítica | Pendiente | Es posible volver al sitio anterior si ocurre un fallo grave |

## Fase 9: Verificación posterior al lanzamiento

| Acción | Prioridad | Momento | Criterio de finalización |
| --- | --- | --- | --- |
| Rastrear nuevamente el dominio | Crítica | Primeras 24 horas | No aparecen errores nuevos de migración |
| Enviar sitemap en Google Search Console | Alta | Día del lanzamiento | Google acepta y procesa el sitemap |
| Revisar cobertura e indexación | Alta | Primera y segunda semana | Las URLs nuevas o conservadas se indexan correctamente |
| Revisar errores `404` y redirecciones | Alta | Diariamente la primera semana | Los errores relevantes se corrigen rápidamente |
| Vigilar tráfico y conversiones | Alta | Primeras cuatro semanas | No hay caídas anormales sin investigar |
| Revisar canonicals detectados por Google | Alta | Primera y segunda semana | Google selecciona las URLs canónicas esperadas |

## Orden recomendado de ejecución

1. Completar contenidos y archivos pendientes.
2. Auditar Rank Math e indexación.
3. Revisar enlaces internos y navegación.
4. Auditar redirecciones.
5. Regenerar y validar el sitemap.
6. Ejecutar rastreo técnico y pruebas de rendimiento.
7. Preparar copia, staging y plan de reversión.
8. Migrar y ejecutar la verificación posterior al lanzamiento.

## Regla de trabajo

Cada fase debe cerrarse con evidencia verificable antes de continuar: conteos, URLs probadas, códigos HTTP, capturas de configuración o actualización de los archivos de control correspondientes.
