# Optimización de rendimiento — comparación Lighthouse

Fecha de cierre: **19 de octubre de 2026**  
Entorno: **WordPress local `http://localhost:8082/`**  
Herramienta: **Lighthouse 13.5.0**  
Estado de producción: **sin cambios; las optimizaciones permanecen en el proyecto local**.

## Resultado

- Score móvil mediano de las 9 rutas: **72 → 85**.
- Promedio móvil: **71.9 → 85.6**.
- Home móvil, mediana de tres ejecuciones: **69 → 81**.
- Home escritorio: **89 → 94**.
- Peso inicial del home: **15.5 MB → 2.4 MB**, una reducción de **85%**.
- LCP móvil: antes había **8/9** rutas deficientes; después quedaron **0 buenas**, **4 por mejorar** y **5 deficientes**.
- CLS bueno: **8/9 → 9/9**. TBT bueno: **9/9**.

Los resultados son pruebas de laboratorio en el entorno local. En producción el hosting, caché y latencia real pueden cambiar las cifras, pero la reducción de bytes y solicitudes pesadas sí corresponde a los recursos servidos.

## Comparación por página

| Página | Score | LCP | Transferencia | Cambio de peso | CLS |
|---|---:|---:|---:|---:|---:|
| [Home](http://localhost:8082/) | 69 → **81** | 4.8 s → **4.5 s** | 15.5 MB → **2.4 MB** | −85% | 0.162 → 0.066 |
| [Programs](http://localhost:8082/saber_college_programs/) | 64 → **85** | 6.5 s → **4.0 s** | 1.8 MB → **0.7 MB** | −63% | 0.000 → 0.000 |
| [Professional Nursing](http://localhost:8082/saber_college_programs/professional-nursing-program/) | 63 → **84** | 13.3 s → **4.1 s** | 6.8 MB → **1.7 MB** | −74% | 0.000 → 0.000 |
| [Physical Therapist Assistant](http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/) | 83 → **86** | 4.0 s → **4.0 s** | 5.9 MB → **1.6 MB** | −73% | 0.027 → 0.000 |
| [Admissions and Tuition](http://localhost:8082/admissions-and-tuition/) | 73 → **89** | 5.3 s → **3.5 s** | 0.6 MB → **0.5 MB** | −12% | 0.000 → 0.000 |
| [Student Services](http://localhost:8082/student-services/) | 63 → **81** | 10.8 s → **4.4 s** | 1.7 MB → **0.7 MB** | −62% | 0.000 → 0.000 |
| [General](http://localhost:8082/general/) | 72 → **80** | 10.8 s → **4.8 s** | 2.0 MB → **0.8 MB** | −60% | 0.026 → 0.000 |
| [Landing PNP](http://localhost:8082/lp/pnp/) | 81 → **93** | 4.1 s → **3.0 s** | 0.4 MB → **0.4 MB** | +16% | 0.000 → 0.000 |
| [Landing PTA](http://localhost:8082/lp/pta/) | 79 → **91** | 4.4 s → **3.3 s** | 0.4 MB → **0.5 MB** | +17% | 0.000 → 0.000 |

## Cambios ejecutados

- Se generaron variantes WebP paralelas para las imágenes usadas por las rutas medidas. Conservan exactamente las dimensiones originales; los archivos pequeños se codificaron sin pérdida y las fotografías grandes con calidad alta. Los originales siguen intactos y el servidor los usa como fallback.
- Se añadieron fuentes locales WOFF2 de Nunito y Nunito Sans con `font-display: swap`; dejaron de solicitarse archivos de Google Fonts.
- Los hero críticos reciben una precarga específica y prioridad alta, por lo que Lighthouse confirma que el LCP se descubre desde el documento inicial.
- El video del home usa póster y `preload=none`; su archivo se solicita cuando el visitante se acerca a la sección y vuelve a reproducirse correctamente.
- Elementor recibió un refresco de CSS después del cambio de entrega de recursos.

## Calidad de imágenes

- Objetivos revisados: **40**.
- Variantes activas: **38**; **2** recursos conservaron su formato original porque WebP no reducía al menos 5%.
- Peso agregado de los recursos objetivo: **23.3 MB → 3.2 MB**.
- Todas las variantes activas conservan las dimensiones originales. Se compararon visualmente los diez archivos más pesados y no se observó pixelación ni degradación apreciable.

## Validación funcional

- **18** comprobaciones: 9 rutas en escritorio y móvil.
- Respuestas no 200: **0**; errores JavaScript: **0**; errores de consola: **0**; solicitudes fallidas: **0**.
- Imágenes rotas: **0**; desbordamientos horizontales: **0**; problemas de H1: **0**.
- Solicitudes a Google Fonts: **0**; respuestas WebP verificadas por el navegador: **214**.
- El formulario del home avanzó al segundo paso en la prueba automatizada sin enviar información.
- Los datos `_elementor_data` y `post_content` del home permanecen idénticos al respaldo: Elementor sigue editable y el contenido no cambió.

## Acciones revisadas y no aplicadas

- No se difirió jQuery ni se eliminó jQuery Migrate: el ahorro probable era pequeño y podía introducir fallos en Elementor, formularios o widgets.
- No se cargó CSS de Elementor de forma asíncrona ni se redujeron contenedores: Lighthouse ya aprueba el tamaño del DOM y esos cambios podían producir parpadeo visual o afectar la edición.
- No se actualizaron plugins solamente para mejorar el score. Esa tarea requiere una regresión independiente en staging.
- No se añadió caché de página específica del entorno local, porque el comportamiento debe definirse según el hosting de producción.

## Evidencia

- `raw/`: 12 resultados Lighthouse posteriores, incluido el home tres veces en móvil y una en escritorio.
- `performance-inventory-after.json`: inventario consolidado antes/después.
- `performance-page-comparison.csv`: comparación reutilizable por ruta.
- `functional-visual-qa.json`: validación de navegador en 18 combinaciones.
- `home-desktop-after.png` y `home-mobile-after.png`: capturas posteriores.
- `../performance-image-quality-comparison-20261018.jpg`: comparación visual de calidad.
- `../backups/performance-20261018/`: respaldo de base de datos, Elementor, contenido y variantes anteriores.
