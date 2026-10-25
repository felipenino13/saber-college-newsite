# Revisión de accesibilidad — 21 de octubre de 2026

## Alcance

Se revisaron 16 páginas representativas en escritorio (1440 px) y móvil (390 px), para un total de 32 ejecuciones. El conjunto incluyó home, programas, páginas de Nursing y PTA, admisiones, aplicación, acreditación, servicios estudiantiles, blog, contacto, encuesta, las cuatro landings y el sistema de diseño.

La auditoría combinó axe con WCAG A/AA, navegación automatizada por teclado, reflujo a la mitad del ancho y a 320 px, inventario de imágenes y pruebas específicas de formularios, acordeones, menú móvil y selector de país.

## Resultado final

- 0 ejecuciones con violaciones de axe.
- 0 saltos incorrectos de jerarquía H1–H6.
- 0 campos visibles sin etiqueta accesible.
- 0 controles enfocables sin nombre accesible.
- 0 páginas con desbordamiento horizontal en el ancho original o en la prueba de reflujo.
- 0 páginas con contenido animado activo cuando `prefers-reduced-motion: reduce` está habilitado.
- 0 imágenes sin atributo `alt`; las imágenes decorativas revisadas usan `alt=""`.
- 0 identificadores HTML duplicados después de la corrección de los estilos dinámicos del loop.
- 0 errores de consola durante las 32 ejecuciones.

Los nueve formularios públicos encontrados tienen etiquetas asociadas, identificadores únicos y mensajes nativos o programáticamente asociados al intentar validar campos obligatorios vacíos. No se enviaron leads durante la prueba.

Los 15 disparadores de acordeón probados respondieron a `Enter`, y el menú móvil cambió correctamente su estado expandido usando el teclado. El selector estático de país del teléfono quedó expuesto como imagen informativa, porque la instancia actual no contiene un desplegable interactivo.

## Ajustes aplicados

- Se corrigieron contrastes insuficientes en Admissions & Tuition, SABER Survey, las landings heredadas y el sistema de diseño.
- Se corrigió la jerarquía de títulos en Programs, el loop del blog y la landing de campus.
- Se agregó un título accesible al iframe de Google Maps.
- Se corrigieron los identificadores repetidos de First Name y Last Name en dos formularios heredados.
- Se añadió foco visible común, compatibilidad global con movimiento reducido y landmark principal para las landings con plantilla Canvas.
- Se corrigió la semántica del selector de país, los nombres de la paginación FooTable y los identificadores repetidos generados por el loop de Elementor.

Los cambios nativos de Elementor conservaron el texto visible, los identificadores de nodo y el número de widgets en las ocho páginas o plantillas modificadas. La segunda ejecución del script de corrección produjo cero cambios, confirmando que es idempotente.

## Pendiente manual

- Recorrer los componentes con NVDA en Windows o VoiceOver en macOS para confirmar la experiencia hablada de menú, acordeones y tablas. No se encontraron modales, carruseles ni tabs en el inventario Elementor revisado.
- Confirmar visualmente el indicador de foco al entrar al iframe de Google Maps. El iframe es de otro dominio y la prueba automatizada no puede inspeccionar el foco interno con fiabilidad.

## Evidencia técnica

- `accessibility-audit-20261021/axe-and-keyboard-final.json`
- `accessibility-audit-20261021/interactions-and-forms.json`
- `accessibility-audit-20261021/image-alt-inventory.json`
- `accessibility-audit-20261021/elementor-structure-validation.json`
- `../scripts/fix-site-accessibility.php`
- `../wordpress/wp-content/mu-plugins/saber-accessibility.php`
