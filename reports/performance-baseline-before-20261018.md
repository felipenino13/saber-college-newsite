# Línea base de rendimiento — Lighthouse

Fecha del corte: **18 de octubre de 2026**  
Entorno: **WordPress local `http://localhost:8082/`**  
Herramienta: **Lighthouse 13.5.0**  
Alcance: **auditoría de laboratorio de solo lectura; todavía no se aplicaron optimizaciones**.

## Resumen

- Score móvil mediano de las 9 rutas representativas: **72/100**; promedio: **71.9/100**.
- Home móvil, mediana de tres ejecuciones: **69/100**.
- Home escritorio: **89/100**.
- LCP móvil: **0 bueno**, **1 por mejorar** y **8 deficientes**.
- CLS dentro del rango bueno: **8/9**. El home queda en **0.162** por cambios de fuente, formulario y contenido del hero.
- TBT dentro del rango bueno: **9/9**. JavaScript no es el principal cuello de botella inicial.
- Página más pesada: **Home, 15.5 MB**.
- LCP más lento: **Professional Nursing, 13.3 s**.

Estas cifras son datos simulados de laboratorio en el computador local. No sustituyen Core Web Vitals reales de usuarios ni representan exactamente la latencia del hosting de producción.

## Inventario por página

| Página | Score | FCP | LCP | TBT | CLS | Transferencia | Solicitudes |
|---|---:|---:|---:|---:|---:|---:|---:|
| [Home](http://localhost:8082/) | 69 | 2.3 s | 4.8 s | 46 ms | 0.162 | 15.5 MB | 94 |
| [Programs](http://localhost:8082/saber_college_programs/) | 64 | 3.7 s | 6.5 s | 102 ms | 0.000 | 1.8 MB | 63 |
| [Professional Nursing](http://localhost:8082/saber_college_programs/professional-nursing-program/) | 63 | 3.2 s | 13.3 s | 109 ms | 0.000 | 6.8 MB | 84 |
| [Physical Therapist Assistant](http://localhost:8082/saber_college_programs/physical-therapist-assistant-program/) | 83 | 2.3 s | 4.0 s | 122 ms | 0.027 | 5.9 MB | 83 |
| [Admissions and Tuition](http://localhost:8082/admissions-and-tuition/) | 73 | 2.9 s | 5.3 s | 105 ms | 0.000 | 0.6 MB | 57 |
| [Student Services](http://localhost:8082/student-services/) | 63 | 3.0 s | 10.8 s | 177 ms | 0.000 | 1.7 MB | 62 |
| [General](http://localhost:8082/general/) | 72 | 2.1 s | 10.8 s | 34 ms | 0.026 | 2.0 MB | 59 |
| [Landing PNP](http://localhost:8082/lp/pnp/) | 81 | 3.0 s | 4.1 s | 62 ms | 0.000 | 0.4 MB | 46 |
| [Landing PTA](http://localhost:8082/lp/pta/) | 79 | 2.9 s | 4.4 s | 55 ms | 0.000 | 0.4 MB | 46 |

## Recursos más pesados

| Recurso | Tipo | Peso | Páginas medidas |
|---|---|---:|---:|
| `NURSING-IA-VIDEO-1.webm` | Media | 4.3 MB | 1 |
| `PTA_ago18.png` | Image | 3.0 MB | 2 |
| `SABER_Blog-_PTA_AGO26-e1787860862637.png` | Image | 2.6 MB | 1 |
| `lanzamiento-sabri_vertv2.png` | Image | 1.8 MB | 1 |
| `call_1BXzmQ66IcvZKJQrMDe2mG1f.png` | Image | 1.5 MB | 1 |
| `help-venezuela.jpg` | Image | 1.4 MB | 1 |
| `general-overview-hero-web.png` | Image | 1.4 MB | 1 |
| `call_FB4Bt31w20xpQ6iPYUHKeIgI.png` | Image | 1.2 MB | 1 |
| `student-services-hero-web.png` | Image | 1.1 MB | 1 |
| `call_lKJ4lRMzKDwgMzLXVhEeMFWG.png` | Image | 1.1 MB | 2 |
| `img_Nurse2-1024x572.png` | Image | 0.8 MB | 1 |
| `lanzamiento-sabri_Landv3-1024x576.png` | Image | 0.8 MB | 2 |

El home carga un video WebM de aproximadamente **4.3 MB** y cinco PNG principales de entre **1.1 y 2.6 MB**. Lighthouse estima cerca de **7.0 MB** de ahorro posible solamente mediante entrega de imágenes. El hero se usa como fondo CSS, por lo que el navegador no descubre temprano el recurso LCP ni puede asignarle `fetchpriority="high"` de forma directa.

## Inventario técnico observado

- WordPress con Hello Elementor y Elementor/Pro Elements.
- **94 solicitudes** en el home móvil y aproximadamente **15.5 MB** transferidos.
- El home contiene aproximadamente **1055 nodos DOM**.
- Fuentes externas: Nunito y Nunito Sans con muchas variantes solicitadas mediante Google Fonts.
- Servicios y scripts externos observados: Trustindex, Elfsight, JSDelivr/`intl-tel-input`, Ipify e ipinfo.
- Scripts globales relevantes: jQuery, Elementor, Pro Elements, AI Engine/Chatbot y recursos del formulario telefónico.
- Oportunidad estimada en el home: **7.0 MB en imágenes**, alrededor de **47 KiB de JavaScript sin usar**, **12 KiB de CSS sin usar** y hasta **620 ms** por recursos de bloqueo de renderizado.

## Lista priorizada de acciones propuestas — no ejecutadas

| Prioridad | Acción propuesta | Evidencia | Resultado esperado | Riesgo/validación |
|---|---|---|---|---|
| P0 | Convertir y redimensionar los PNG grandes del home y programas a WebP/AVIF, generando variantes responsivas | Home 15.5 MB; PNP 6.8 MB; PTA 5.9 MB; ahorro de imágenes del home cercano a 6.9 MB | Menor transferencia y LCP más rápido | Comparar calidad visual y verificar cada breakpoint en Elementor |
| P0 | Tratar el recurso LCP del hero como imagen prioritaria o precargarlo; evitar que dependa solo de un fondo CSS | Lighthouse no lo descubre en el HTML inicial y no tiene prioridad alta | Reducir especialmente los LCP de 4–13 s | Mantener el diseño y la edición nativa de Elementor |
| P0 | Evitar la descarga inicial del video `NURSING-IA-VIDEO-1.webm`; usar `preload="none"`, póster y carga al acercarse o interactuar | El archivo aporta cerca de 4.3 MB al home | Gran reducción del peso inicial | Comprobar autoplay, controles y experiencia móvil |
| P1 | Consolidar Nunito/Nunito Sans, limitar pesos usados, autoalojar WOFF2 y precargar solo la fuente crítica | Fuentes bloquean renderizado y explican parte del CLS 0.162 del home | Mejor FCP y estabilidad visual | Ajustar métricas de fallback para evitar cambios de línea |
| P1 | Reducir recursos que bloquean renderizado: CSS de widgets no usados, jQuery Migrate y estilos Elementor no críticos | Lighthouse estima hasta 620 ms en el home | Mejor FCP/LCP | Revisar formularios, menús, acordeones y editor de Elementor |
| P1 | Cargar de forma condicional o después de interacción Trustindex, Elfsight, chatbot e internacionalización telefónica | Se cargan dependencias y llamadas externas durante el arranque | Menos solicitudes y trabajo inicial | No retrasar funciones necesarias para conversión |
| P1 | Reservar dimensiones del hero, mascota y pasos del formulario; estabilizar el cambio de fuentes | CLS del home 0.162 | Llevar CLS por debajo de 0.1 | Verificar móvil, tablet y escritorio |
| P2 | Revisar page cache, object cache, OPcache, compresión y tiempo de respuesta del hosting | Documento inicial local cercano a 610 ms en la primera prueba | Mejor TTFB/FCP | Volver a medir en staging/producción; el servidor local no representa el hosting |
| P2 | Reducir anidación de contenedores y nodos repetidos en loops del home | Aproximadamente 1,055 nodos DOM | Menos trabajo de estilo/layout | Mantener la estructura editable y comparar texto/widgets |
| P2 | Revisar actualizaciones pendientes de Elementor, Rank Math, AI Engine y Trustindex en staging | Inventario de plugins muestra versiones pendientes | Posibles correcciones y mejoras acumuladas | Hacer respaldo y regresión visual/funcional; no actualizar solo por puntuación |

## Orden sugerido para la siguiente fase

1. Imágenes y video del home.
2. Imágenes y LCP de Professional Nursing, Student Services, General y Programs.
3. Descubrimiento/prioridad de los hero y corrección del CLS del home.
4. Fuentes y recursos que bloquean renderizado.
5. Scripts externos y carga condicional.
6. Caché/servidor, DOM y actualizaciones en un entorno de prueba.
7. Repetir tres ejecuciones móviles por página y comparar contra esta línea base.

## Evidencia conservada

- `raw/`: resultados JSON de todas las pruebas y reportes HTML completos del home.
- `run-manifest.json`: versión, URL, perfil y score de cada ejecución.
- `performance-inventory-before.json`: inventario consolidado por página y recurso.
- `performance-page-inventory-before.csv`: tabla reutilizable para la comparación posterior.
- `lighthouse-before-overview.html` y `.png`: fotografía visual de esta línea base.
- `active-plugins.json` y `active-theme.json`: inventario local de la plataforma.
