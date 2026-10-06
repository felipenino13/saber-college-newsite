# Net Price Calculator - Design System

Fecha: 2026-10-05  
Ruta tecnica: `http://localhost:8082/saber-net-price-calculator-app/`  
Pagina canonica: `http://localhost:8082/general/net-price-calculator/`  
Plugin: `saber-net-price-calculator` version 1.2.0

## Alcance

- Se aplico el Design System SABER a la calculadora alojada por el plugin.
- No se modificaron textos, datos, preguntas, respuestas, formulas ni comportamiento de los seis pasos.
- El archivo original `app/Net-Price-Calculator.html` conserva el SHA-256 `4DC8F93FDE0AF790DA08D280C7815383EC6558C5B09C3A081E3C98DDFC364ECF`.
- La capa visual se inyecta en tiempo de ejecucion desde `assets/saber-design-system.css`.
- Nunito se sirve localmente desde el plugin y su licencia SIL OFL se incluye en `assets/OFL-Nunito.txt`.

## Componentes actualizados

- Cabecera institucional sobre superficie blanca con geometria y acento amarillo.
- Indicador de progreso numerado con estados activo y completado.
- Introducciones con superficie Sky y linea funcional azul.
- Preguntas agrupadas en tarjetas responsive.
- Radios, campos numericos y selectores con foco visible.
- Botones primarios amarillos y acciones secundarias en azul profundo.
- Resumen de respuestas y tabla de resultado con jerarquia financiera clara.
- Avisos y notas finales sobre superficies Mist y Yellow Soft.

## Verificacion

- Flujo completo probado desde Step 1 hasta Step 6.
- Resultado de control conservado: `$44,353.00` con los mismos datos de prueba.
- Escritorio: sin desbordamiento horizontal.
- Movil: viewport de 390 x 844 px, tarjetas y resultados en una columna, botones a ancho completo y sin elementos fijos que cubran contenido.
- Fuente local disponible con respuesta HTTP 200 y tipo `font/woff2`.
- Sin errores ni advertencias en consola durante el recorrido.
- La ruta tecnica mantiene `X-Robots-Tag: noindex, nofollow`.
- El shortcode `[saber_net_price_calculator]` esta insertado como widget nativo de Elementor en la pagina canonica.
- El modo embebido elimina la cabecera duplicada, ajusta automaticamente la altura y conserva el formulario responsive.
- El boton y la imagen del bloque de lanzamiento apuntan al ancla `#net-price-calculator`, sin recargar la pagina.
- Rank Math gestiona el `301` de `/Net-Price-Calculator.html` a `/general/net-price-calculator/` mediante la redireccion ID `123`.
- La URL heredada fue probada con mayusculas y minusculas; ambas responden `301` con `X-Redirect-By: Rank Math`.

## Pendiente fuera de este alcance

- Revisar o actualizar los datos regulatorios `2018-19` solo con una fuente aprobada por SABER College.
