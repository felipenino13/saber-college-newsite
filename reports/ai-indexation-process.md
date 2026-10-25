# Proceso de indexación para IA — SABER College

## Propósito

Esta bitácora registra el diagnóstico, las decisiones, los cambios y la evidencia relacionados con la visibilidad de SABER College en buscadores y asistentes de inteligencia artificial.

El trabajo del 21 de octubre de 2026 se realizó únicamente en el WordPress local. El sitio público no fue modificado.

## Decisión sobre indexación

El usuario solicitó expresamente no configurar local ni staging como `noindex`, para evitar que esa configuración llegue accidentalmente al dominio definitivo durante la migración.

Por esa razón:

- `blog_public` permaneció en `1`;
- no se cambiaron las reglas `index` o `noindex` de Rank Math;
- no se modificó `robots.txt` para bloquear el entorno;
- no se incluyó una condición automática por dominio o variable de entorno;
- la evidencia antes/después quedó guardada en `reports/ai-indexation-after-20261021/noindex-safeguard.json`.

## Entornos

| Entorno | Uso | Acción en este proceso |
|---|---|---|
| `http://localhost:8082` | Desarrollo y validación | Mejoras aplicadas y verificadas; política de indexación intacta |
| Dominio temporal de SiteGround | Revisión pública | Sin cambios |
| `https://sabercollege.edu` | Producción definitiva | Sin cambios |

## Respaldo previo

Se creó `reports/backups/ai-indexation-20261021/` con:

- `wordpress-before.sql`: exportación completa de la base de datos;
- `rank-math-options-titles-before.json`;
- `rank-math-options-general-before.json`;
- `saber-rank-math-before.php`;
- `elementor-887-before.json`;
- copia inicial del checklist.

El primer intento de exportar la base desde el contenedor de WordPress no funcionó porque ese contenedor no incluye `mysqldump`. La exportación se realizó correctamente desde `sabercollege_db`.

## Estado inicial

El rastreo inicial encontró 132 URLs en los sitemaps.

| Indicador | Antes |
|---|---:|
| URLs del sitemap | 132 |
| Respuestas distintas de HTTP 200 | 7 |
| Páginas 200 sin meta description | 8 |
| Páginas 200 sin canonical | 0 |
| Páginas 200 sin exactamente un H1 | 23 |
| Bloques JSON-LD inválidos | 2 |
| Apariciones de imágenes sin `alt` | 574 |

Las siete respuestas 404 correspondían a entradas eliminadas o categorías antiguas que permanecían en la caché XML de Rank Math.

## Cambios aplicados

### Entidad institucional

Se amplió `wordpress/wp-content/mu-plugins/saber-rank-math.php` para que Rank Math genere una entidad `CollegeOrUniversity` única y conectada. Incluye:

- nombre público y nombre legal;
- año de fundación;
- dirección, teléfono y correo institucional;
- perfiles sociales oficiales;
- identificadores CIE 1400 y COE 220500;
- URLs dinámicas basadas en `home_url()` para que el dominio se adapte durante la migración.

El directorio actual de COE debe consultarse siempre como fuente primaria. No se codificó una afirmación simplificada sobre su estado, porque el registro público puede incluir condiciones o cambios de estatus.

### Programas académicos

Se añadió `EducationalOccupationalProgram` a:

- Professional Nursing: 80 créditos y 1,725 horas;
- Physical Therapist Assistant: 76 créditos y 1,785 horas.

Cada programa se conecta con la entidad institucional, su página, la credencial y fuentes primarias CIE más NLN CNEA o CAPTE. La página general de programas incluye un `ItemList` con ambos programas.

### Metadatos y estructura

El script `scripts/apply-ai-indexation-improvements.php` realizó cambios idempotentes:

- corrigió las meta descriptions de dos artículos que producían JSON-LD inválido;
- añadió meta descriptions SEO a ocho categorías activas;
- convirtió a H2 el H1 interno de 23 artículos, conservando como H1 único el título del tema;
- completó textos alternativos de imágenes institucionales, imágenes destacadas e imagen de testimonio;
- configuró Rank Math como `CollegeOrUniversity` y añadió la descripción institucional;
- limitó los tipos de contenido de `llms.txt` a páginas.

### `llms.txt`

El archivo pasó de una lista automática con artículos y extractos débiles a 26 páginas institucionales seleccionadas. Ahora incluye:

- resúmenes basados en las meta descriptions;
- programas, admisiones, costos, acreditación, servicios y políticas;
- datos básicos de la institución;
- enlaces a fuentes oficiales CIE, COE, NLN CNEA y CAPTE;
- cero apariciones de `???` o `Home -`.

### Idiomas

Se confirmaron 12 parejas de artículos inglés/español. Las 24 URLs ahora incluyen:

- `lang="en-US"` o `lang="es-US"` según corresponda;
- `hreflang="en-US"`;
- `hreflang="es-US"`;
- `hreflang="x-default"` hacia la versión inglesa.

### Enlaces de acreditación

En la página Elementor de acreditación, los widgets de imagen CIE, COE, CAPTE y NLN CNEA enlazan a sus fuentes primarias. Los enlaces tienen nombre accesible y se abren en una pestaña nueva.

No se cambió el texto visible. La estructura conservó 54 nodos y 24 widgets.

### Caché del sitemap

El comando CLI de Rank Math falló inicialmente porque `FS_CHMOD_FILE` no estaba definido en ese contexto. Se configuró el caché de sitemap de Rank Math en modo base de datos dentro del MU plugin. Después de ese ajuste, el comando generó correctamente el sitemap y las peticiones web continuaron respondiendo 200.

## Resultado posterior

| Indicador | Antes | Después |
|---|---:|---:|
| URLs del sitemap | 132 | 125 |
| Respuestas distintas de HTTP 200 | 7 | 0 |
| Páginas 200 sin meta description | 8 | 0 |
| Páginas 200 sin canonical | 0 | 0 |
| Páginas 200 sin exactamente un H1 | 23 | 0 |
| Bloques JSON-LD inválidos | 2 | 0 |
| Apariciones de imágenes sin `alt` | 574 | 0 |

## Validaciones

- Rastreo completo de las 125 URLs del sitemap: todas HTTP 200.
- JSON-LD parseable en todas las páginas rastreadas.
- Una entidad `CollegeOrUniversity` por página.
- Dos entidades `EducationalOccupationalProgram` con sus datos esperados.
- 24 páginas bilingües con `lang` y `hreflang` correctos.
- `llms.txt` con 26 páginas curadas y sin caracteres dañados.
- HTTP 200 para Googlebot, GPTBot, OAI-SearchBot, ChatGPT-User y ClaudeBot.
- Elementor antes/después: mismo número de nodos y widgets, texto visible idéntico.
- Escritorio y móvil en acreditación: sin overflow, sin errores de consola, un H1 y cuatro enlaces oficiales válidos.
- Segunda ejecución del script de aplicación: cero cambios y cero errores.
- Validación consolidada: `passed: true`.

## Archivos principales

- Implementación de schema, idiomas y `llms.txt`: `wordpress/wp-content/mu-plugins/saber-rank-math.php`
- Cambios idempotentes de WordPress: `scripts/apply-ai-indexation-improvements.php`
- Rastreador reproducible: `scripts/audit-ai-indexation.py`
- Validador reproducible: `scripts/validate-ai-indexation.py`
- QA visual: `reports/qa-ai-indexation.mjs`
- Evidencia inicial: `reports/ai-indexation-before-20261021/`
- Evidencia final: `reports/ai-indexation-after-20261021/`
- Checklist: `reports/ai-indexation-checklist.md`
- Checklist final de lanzamiento: `reports/pre-launch-checklist.md`

## Pendiente de producción

Cuando el sitio aprobado se migre a `https://sabercollege.edu`, se debe repetir el rastreo con el dominio definitivo, comprobar canonical/schema/sitemap/`llms.txt`, validar datos estructurados desde una herramienta pública y enviar el sitemap a Google Search Console y Bing Webmaster Tools.

La publicación o migración no forma parte de este cambio local.
