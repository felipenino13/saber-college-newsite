# Auditoría responsive, navegación y experiencia de usuario

Fecha: 21 de octubre de 2026  
Entorno: local (`http://localhost:8082`)  
Estado: completada; cambios locales, no publicados

## Alcance

- 20 páginas representativas de las plantillas principales.
- 6 anchos: 360, 390, 768, 1024, 1366 y 1920 píxeles.
- Chrome y Microsoft Edge: 120 ejecuciones por navegador, 240 en total.
- Rastreo de las 43 páginas publicadas para enlaces, anclas, teléfonos, correos y documentos.
- Pruebas de menú de escritorio, menú móvil, submenús y cierre mediante teclado.
- Pruebas simuladas de formulario sin transmitir leads reales.

## Resultado final

Chrome y Edge terminaron con los mismos resultados:

- 0 respuestas inesperadas.
- 0 desbordamientos horizontales.
- 0 textos cortados.
- 0 imágenes deformadas.
- 0 controles visibles menores de 24 × 24 píxeles.
- 0 enlaces o botones visibles sin nombre.
- 0 elementos fijos que oculten contenido o controles.
- 0 errores de consola.

Las páginas estándar incluyen breadcrumbs y footer. La portada no necesita breadcrumb; la página de agradecimiento tampoco. Las cuatro landings (`/lp/`, `/lp/campus-location/`, `/lp/pnp/` y `/lp/pta/`) conservan su navegación y cierre simplificados de conversión de forma intencional.

## Correcciones aplicadas

- Se amplió el área táctil de iconos sociales, enlaces del footer y el acceso a Canvas.
- Se añadieron estados perceptibles `hover` y `active` a los botones enlazados, conservando el foco visible y `prefers-reduced-motion`.
- Se añadió autocompletado semántico a campos de nombre, apellido, correo y teléfono.
- Los siete campos telefónicos probados ahora solicitan teclado telefónico en móvil.
- Se reparó el destino `#request-info` de `/lp/` mediante el ID nativo de Elementor.
- Se añadió un nombre accesible al icono que abre el formulario PDF de solicitud de transcript.
- Se actualizaron destinos antiguos de FAFSA, Federal Student Aid y SEVP.
- Se retiró el hipervínculo de DL Training Solutions porque su dominio ya no responde; el texto visible se conservó.

## Navegación, formularios y estados

- Menú principal de escritorio: abre y cierra por teclado.
- Menú y submenú móvil: abren y cierran por teclado.
- Estados de botones: `hover`, `focus` y `active` verificados.
- Formulario: `loading`, éxito con redirección y error con recuperación del botón verificados mediante respuestas simuladas; no se envió ningún lead.
- Página 404, búsqueda sin resultados y página de agradecimiento: respuesta, H1, contenido y landmark principal correctos.
- 9 páginas con formulario, 46 campos y 34 campos personales revisados; ninguno quedó sin el autocompletado esperado.

## Enlaces y documentos

- 43 páginas rastreadas.
- 3.444 apariciones de enlaces; 130 destinos HTTP únicos.
- 0 enlaces internos rotos y 0 anclas internas rotas.
- 0 teléfonos o correos mal formados.
- 29 PDF locales responden `200` con tipo `application/pdf`.
- Los tres formularios Jotform enlazados desde Manuales responden `200`.
- Un PDF oficial de `ed.gov` agotó el tiempo del robot; la URL permanece vigente. Otros tres portales gubernamentales respondieron `403` al robot, aunque sus destinos oficiales fueron verificados manualmente.
- La página del design system contiene siete botones de demostración con `#`; se conservaron porque la página está marcada `noindex` y no pertenece a la navegación pública.

## Editabilidad en Elementor

Antes de modificar las páginas 25, 34, 898, 902, 911 y 1638 se guardó `_elementor_data`. Después se compararon estructura y contenido:

- mismo número de nodos;
- mismo número de widgets;
- mismo texto visible;
- script idempotente: una segunda ejecución produjo cero cambios.

## Límites de esta revisión

Safari no está disponible en Windows y Firefox no está instalado en este equipo. Las pruebas se completaron en Chrome y Edge. Conviene repetir una pasada breve en Safari real después de publicar o desde un dispositivo Apple.

## Evidencia

- `responsive-final-chrome.json`
- `responsive-final-edge.json`
- `links-and-navigation.json`
- `navigation-and-states.json`
- `mobile-form-fields-final.json`
- `elementor-preservation.json`
- `contact-sheet-360.jpg`
- `contact-sheet-768.jpg`
- `contact-sheet-1366.jpg`
