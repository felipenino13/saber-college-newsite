# Corrección de estilos del formulario del home

Fecha: 2026-10-16  
Página corregida: `http://localhost:8082/`  
Página de referencia: `http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/`

## Diagnóstico

El formulario y sus estilos sí se habían copiado, pero el bloque CSS seguía limitado a la plantilla original de Elementor:

- Página original: `.elementor-2015`
- Formulario original: `.elementor-element-f698ff6`
- Página home real: `.elementor-7`
- Formulario home real: `.elementor-element-b7159a9`

Por esa diferencia, el navegador ignoraba las reglas copiadas dentro del home. Los ajustes básicos definidos en el widget seguían activos, pero no se aplicaban las tarjetas de selección, el espaciado, las sombras, la altura de campos ni los estados de foco y selección de la referencia.

## Corrección

Se actualizaron únicamente los selectores del widget HTML `8d28c13` del home:

- 30 apariciones del alcance de página.
- 28 apariciones del selector del formulario.
- Se retiró una regla sin uso para el texto legal de la plantilla, porque ese widget no existe en el home y no correspondía vincularla a otro contenido.

No se modificaron los campos, el orden de pasos, las acciones de envío, el redireccionamiento, los scripts de integración ni los textos. La página PTA 872 y la plantilla 2015 permanecieron intactas.

## Verificación visual y funcional

- Primer paso: programas presentados como tarjetas seleccionables.
- Segundo paso: campos personales con el mismo tratamiento visual que la referencia.
- Campos: altura mínima de 48 px, padding de 12 × 14 px, borde de 1 px y radio de 10 px.
- Tarjetas: dos columnas en escritorio y una columna en móvil; radio de 12 px y estado seleccionado amarillo.
- Escritorio 1440 px y móvil 390 px: cero desbordamiento horizontal.
- Formulario visible, un solo paso visible a la vez y sin IDs duplicados.
- Se recorrieron ambos pasos sin enviar datos.
- Cero errores de consola. La prueba headless registró una cancelación del video decorativo `NURSING-IA-VIDEO-1.webm`, ajena al formulario.
- Los conteos de nodos y widgets, los 59 textos visibles y toda la configuración del formulario permanecieron iguales.
- La segunda ejecución fue idempotente.

## Archivos

- Implementación: `scripts/fix-home-form-style-scope.php`
- Resultado consolidado: `reports/home-form-style-fix-result-20261016.json`
- Capturas de los pasos: `reports/home-form-style-desktop-step1-after-20261016.png`, `reports/home-form-style-desktop-step2-after-20261016.png`, `reports/home-form-style-mobile-step1-after-20261016.png` y `reports/home-form-style-mobile-step2-after-20261016.png`
- Respaldo y exportaciones: `reports/backups/home-form-style-20261016/`
- Respaldo adicional dentro del contenedor: `/tmp/home-form-style-before-20261016-132225.json`
