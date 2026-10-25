# Rediseño local de `/lp/pnp/`

Fecha: 2026-10-14  
Página local: `http://localhost:8082/lp/pnp/`  
WordPress: página 1640  
Estado: aplicada localmente, editable en Elementor, no publicada en producción

## Alcance

Se rediseñó la landing de Professional Nursing con el contenido que ya existía en la página. Se tomó como referencia de conversión la landing pública de Professional Nursing y se aplicó el sistema visual documentado de SABER College: Nunito, azul institucional, amarillo para acciones, superficies claras, fotografía relacionada con estudiantes y contenedores con radios suaves.

No se redactó contenido promocional nuevo. La comparación automatizada encontró las 64 cadenas visibles originales dentro del resultado final. Se retiró únicamente el marcador editorial `// Eliminar` que estaba visible al final de la página.

## Implementación

- Diez secciones nativas con 55 contenedores Flexbox editables en Elementor.
- Barra de marca compacta con CTA al formulario.
- Hero de dos columnas con propuesta del programa, beneficios, fotografía y formulario visible.
- Overview dividido con imagen.
- Seis beneficios convertidos en tarjetas escaneables.
- Cuatro áreas de aprendizaje en una cuadrícula responsive.
- Currículo en un momento de confianza azul con imagen y cuatro bloques académicos.
- Carrera, requisitos de admisión, FAQ y acreditación con jerarquía diferenciada.
- Cuatro enlaces CTA escritos dentro de widgets de texto se convirtieron en botones nativos de Elementor.
- El texto existente `Send My Request` se trasladó al botón real del formulario.
- Los campos `First Name` y `Last Name` ahora tienen IDs únicos para evitar colisiones en etiquetas y envíos.
- Se añadieron textos alternativos a las seis imágenes reutilizadas.
- Se conservó la plantilla `elementor_canvas` y el modo `builder`.

## Recursos reutilizados

- Imágenes 1652, 1659, 1660 y 1661 de la colección existente de la landing PNP.
- Logo 1662 y sello de acreditación 1651.
- Fondos del design system `path-light` 2064 y `path-warm` 2065.

No fue necesario generar una imagen nueva.

## Verificación

- HTTP 200 en la URL local.
- Una sola etiqueta H1; ocho H2 y trece H3.
- Un formulario Elementor visible con cuatro campos y etiquetas asociadas.
- Sin IDs HTML duplicados.
- Los CTA llegan a `#form-field` y el destino existe.
- Sin enlaces vacíos `href="#"` ni enlaces anidados.
- Sin imágenes rotas después de cargar el contenido diferido.
- Todas las imágenes tienen texto alternativo.
- Sin desbordamiento horizontal en 390 px.
- Sin errores ni advertencias en la consola del navegador durante la revisión.
- El script se ejecutó una segunda vez y respondió como operación idempotente.
- Home 7, header 16 y footer 53 conservaron sus hashes.

El detalle técnico está en `reports/pnp-landing-redesign-result-20261014.json`.

## Archivos

- Script reproducible: `scripts/redesign-pnp-landing.php`
- Copia previa de Elementor: `reports/backups/pnp-landing-redesign-20261014/elementor-before.json`
- HTML previo y posterior: `reports/backups/pnp-landing-redesign-20261014/rendered-before.html` y `rendered-after.html`
- Capturas: `reports/pnp-landing-redesign-20261014-desktop.png` y `reports/pnp-landing-redesign-20261014-mobile.png`

## Nota editorial

La frase existente `Secure your sport today.` se mantuvo literalmente para respetar la instrucción de no editar el contenido. Probablemente requiere una revisión editorial separada si se quería decir `Secure your spot today.`
