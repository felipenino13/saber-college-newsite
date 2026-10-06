# Student Services: aplicacion del Design System 0.2

Fecha: 2026-10-02
Pagina: http://localhost:8082/student-services/
WordPress: ID 34

## Alcance

Redisenada exclusivamente la pagina Student Services, sin cambiar textos, enlaces, URL ni ajustes SEO. Se conserva el header y footer existente, incluido su comportamiento global. Se incorpora la fotografia del campus ya disponible en la biblioteca (ID 1699), sin crear afirmaciones ni contenido editorial nuevo.

## Composicion editable

1. Miga de pan Rank Math centrada.
2. Hero claro con Path Light, titulo original y fotografia del campus con esquinas asimetricas.
3. Tres tarjetas de servicios: Academic Affairs, VISA I-20 Assistance y Career Services.
4. Dos accesos secundarios: Student's Transcript Request y Saber Survey.
5. Seis tarjetas de recursos externos sobre Path Warm.

Estructura: contenedores nativos e-flexbox y 37 widgets Elementor. Tipos: shortcode original de breadcrumbs, divider, heading, image, icon y text-editor. No se utiliza un widget HTML para encapsular el diseno. Fondos SVG IDs 2064/2065 en `uploads/saber-design-system/`, sin habilitar globalmente cargas SVG. El CSS adicional de pagina solo trata el foco, enlaces y ajustes menores; es editable en los ajustes de Elementor.

## Verificacion

- Comparacion automatica contra el respaldo anterior: titulo, shortcode y HTML de los once enlaces identicos.
- Contenido original de WordPress y metadatos Rank Math sin cambios.
- Datos Elementor de home 7, header 16 y footer 53 sin cambios (SHA-256).
- Revision visual de hero, servicios y recursos en escritorio.
- Revision movil: tarjetas apiladas, sin desbordamiento horizontal del documento (355 CSS px efectivos en el navegador de prueba).
- CSS de pagina generado y radio de imagen comprobados en el navegador.
- No se cambio el comportamiento de apertura ni se auditaron los destinos externos, para conservar los enlaces originales.

## Respaldo y mantenimiento

Respaldo original: `database/elementor-backups/student-services-34-before-20261002-113559.json`. Incluye contenido y metadatos, excluido de Git. No publicar este respaldo.

Aplicacion: `scripts/redesign-student-services.php`. Verificacion de regresiones: `scripts/verify-student-services.php`. Ambos se ejecutan con WP-CLI dentro del contenedor; el segundo requiere el respaldo original en `/tmp`.

Para repetir la aplicacion, copiar primero los SVG `design-system-assets/path-light.svg` y `path-warm.svg` a `/tmp` del contenedor. El script preserva el contenido de los widgets identificados, crea un nuevo respaldo y regenera solo los estilos de esta pagina. No ejecutarlo si se desea conservar cambios de maquetacion posteriores hechos desde Elementor.

La maquetacion esta en la base de datos local. Git por si solo no la transporta a otro computador: incluirla en el flujo privado de exportacion/importacion de la base de datos junto con sus medios.
