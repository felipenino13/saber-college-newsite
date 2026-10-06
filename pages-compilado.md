# Comparativo de Páginas: Sitio Público vs. Proyecto Local

Comparación realizada el 24 de septiembre de 2026 entre [pages.md](pages.md) (sitio público) y [pages-new.md](pages-new.md) (WordPress local).

Resumen actualizado al 5 de octubre de 2026: 46 páginas públicas inventariadas y 43 páginas locales publicadas. `/lp/thank-you/` se consolidó con `/thank-you/`, y toda la rama `/lp/` que continúa publicada está configurada como `noindex`. Las landings locales de VESL y Medical Assistant fueron enviadas a la papelera por corresponder a programas retirados; sus URLs requieren una decisión final de redirección equivalente o respuesta `410`. La página local adicional `SABER Design System – Elementor` es una biblioteca interna publicada con `noindex` y no requiere equivalente ni migración como contenido del sitio.

## Cambios Aplicados el 24 de Septiembre de 2026

Las siguientes 20 páginas locales ya se alinearon con su URL pública equivalente. Se actualizaron únicamente los slugs y/o la jerarquía de página; el contenido no fue modificado.

| Página pública | URL local resultante | Estado |
| --- | --- | --- |
| About Us | `/about_saber_college/` | Alineada |
| Contact | `/about_saber_college/contact/` | Alineada |
| Apply To SABER College | `/apply-to-saber-college/` | Alineada |
| Campus Location | `/campus-location/` | Alineada |
| General Overview | `/general/` | Alineada |
| Gallery | `/general/explore-saber-college-gallery/` | Alineada |
| Gainful Employment | `/general/gainful-employment-programs/` | Alineada |
| HEERF Historical Reports | `/general/heerf-quarterly-reports/` | Alineada |
| Leadership | `/general/saber_college_leadership/` | Alineada |
| Distance Education | `/general/saber-college-distance-education/` | Alineada |
| Faculty | `/general/saber-college-faculty/` | Alineada |
| Tuition Payment Options | `/general/tuition-payment-options/` | Alineada |
| VISA I-20 Assistance | `/general/visa-i-20-assistance/` | Alineada |
| Programs | `/saber_college_programs/` | Alineada |
| Professional Nursing Program A.S. | `/saber_college_programs/professional-nursing-program/` | Alineada |
| Admissions - Nursing | `/saber_college_programs/professional-nursing-program/nursing-admissions/` | Alineada |
| Physical Therapist Assistant Program A.S. | `/saber_college_programs/physical-therapist-assistant-program/` | Alineada |
| Admissions - PTA | `/saber_college_programs/physical-therapist-assistant-program/admissions-pta/` | Alineada |
| Accreditation | `/saber-college-accreditation/` | Alineada |
| SABER College Blogs | `/saber_college_blog/` | Alineada |

La tabla está ordenada por atención requerida: primero aparecen las URLs públicas ausentes en local, luego las páginas incompletas o con decisiones pendientes y finalmente las URLs ya alineadas.

| # | Prioridad | Página y URL pública | Equivalente local actual | Estado | Acción recomendada | Comentario |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | Alta | LP - VESL `/lp/vesl/` | Página local en papelera | Programa retirado; la antigua landing ya no está publicada | Definir URL equivalente o respuesta `410` | No se inventó una redirección. Las reglas que terminaban en esta landing se desactivaron. |
| 2 | Alta | Medical Assistant Program `/medical-assistant-program/` | Página local en papelera | Programa retirado; el `301` hacia la landing retirada fue desactivado | Definir URL equivalente o respuesta `410` | Medical Assisting ya no se oferta y no se proporcionó un destino sustituto. |
| 3 | Alta | ESOL Program `/esol-program/` | Página local en papelera | Programa retirado; el `301` hacia la landing retirada fue desactivado | Definir URL equivalente o respuesta `410` | ESOL/VESL ya no se oferta y no se proporcionó un destino sustituto. |
| 4 | Resuelta | Nursing Program corta `/professional-nursing-program/` | LP - Professional Nursing `/lp/pnp/` | Redirección `301` aplicada en Rank Math | Ninguno | La URL pública dirige a la landing equivalente en Elementor. |
| 5 | Resuelta | PTA Program corta `/physical-therapist-assistant-program/` | LP - PTA `/lp/pta/` | Redirección `301` aplicada en Rank Math | Ninguno | La URL pública dirige a la landing equivalente en Elementor. |
| 6 | Resuelta | Safety Plan `/general/safety-plan/` | Safety Plan `/general/safety-plan/` | URL alineada; redirección obsoleta eliminada | Ninguno | La página local responde directamente y ya no redirige a Institutional Plans. |
| 7 | Resuelta | LP - Thank You `/lp/thank-you/` | Thank You Page `/thank-you/` | Página duplicada eliminada; redirección `301` aplicada en Rank Math | Ninguno | `/thank-you/` queda como única página de agradecimiento; la página anterior permanece recuperable en la papelera. |
| 8 | Resuelta | Home `/` | Home `/` | URL alineada | Ninguno | URL ya coincide. |
| 9 | Resuelta | CARES Act `/45-day-report-on-ed-grants/` | CARES Act `/45-day-report-on-ed-grants/` | URL alineada | Ninguno | No requiere redirección a HEERF. |
| 10 | Resuelta | About Us `/about_saber_college/` | About Us `/about_saber_college/` | URL alineada | Ninguno | Es la única página About Us local y la URL canónica. |
| 11 | Resuelta | Contact `/about_saber_college/contact/` | Contact Us `/about_saber_college/contact/` | URL alineada | Ninguno | Lucy Alvarez y sus datos fueron actualizados; se retiró el bloque VESL. |
| 12 | Resuelta | Apply To SABER College `/apply-to-saber-college/` | Apply To SABER College `/apply-to-saber-college/` | URL alineada | Ninguno | `/apply-now/` redirige con `301` a esta pagina. Rediseno local con Design System 0.2 aplicado el 2026-10-02 en Elementor; contenido, listas y Rank Math conservados. |
| 13 | Resuelta | Campus Location `/campus-location/` | Campus Location `/campus-location/` | URL alineada | Ninguno | Página ubicada en la raíz. Lucy Alvarez y sus datos fueron actualizados; se retiró la tarjeta VESL. |
| 14 | Resuelta | General Overview `/general/` | General Overview `/general/` | URL alineada | Ninguno | Conserva la sección institucional pública. |
| 15 | Resuelta | Gallery `/general/explore-saber-college-gallery/` | Gallery `/general/explore-saber-college-gallery/` | URL alineada | Ninguno | Slug y jerarquía coinciden; se retiraron referencias a programas que ya no se ofertan. |
| 16 | Resuelta | Gainful Employment `/general/gainful-employment-programs/` | Gainful Employment `/general/gainful-employment-programs/` | URL alineada | Ninguno | Año fiscal actualizado a 2026-2027. |
| 17 | Resuelta | Graduation Requirements `/general/graduation-requirements/` | Graduation Requirements `/general/graduation-requirements/` | URL alineada | Ninguno | Se retiró la redacción de programas descontinuados y se corrigió un error tipográfico heredado. |
| 18 | Resuelta | HEERF Historical Reports `/general/heerf-quarterly-reports/` | HEERF Historical Reports `/general/heerf-quarterly-reports/` | URL alineada | Ninguno | Conserva la URL pública. |
| 19 | Resuelta | Institutional Plans `/general/institutional-plans/` | Institutional Plans `/general/institutional-plans/` | URL alineada | Ninguno | Slug y jerarquía coinciden. |
| 20 | Resuelta | Manuals `/general/manuals/` | Manuals `/general/manuals/` | URL alineada | Ninguno | Slug y jerarquía coinciden. |
| 21 | Parcial | Net Price Calculator `/general/net-price-calculator/` | Net Price Calculator `/general/net-price-calculator/` | URL alineada; calculadora embebida; `301` heredado aplicado | Confirmar los datos regulatorios 2026-2027 con una fuente aprobada | El shortcode está insertado en Elementor y `/Net-Price-Calculator.html` redirige mediante Rank Math. La lógica original se conserva y los datos internos aún corresponden a `2018-19`. |
| 22 | Resuelta | Leadership `/general/saber_college_leadership/` | SABER College Leadership `/general/saber_college_leadership/` | URL alineada | Confirmar apellido Gaspara | Personal, Board, FAQ y sellos COE/CIE/CAPTE/NLN CNEA actualizados el 2026-10-05. |
| 23 | Resuelta | Distance Education `/general/saber-college-distance-education/` | Distance Education `/general/saber-college-distance-education/` | URL alineada | Ninguno | Se retiraron VESL y la sección obsoleta “Designed for Access and Impact”. |
| 24 | Resuelta | Faculty `/general/saber-college-faculty/` | Faculty `/general/saber-college-faculty/` | URL alineada | Confirmar apellido Gaspara | Listas de Nursing, PTA y General Education actualizadas; VESL Faculty retirado. |
| 25 | Resuelta | VESL: Curso de Ingles `/programa-vesl-online/` | Programs `/saber_college_programs/` | Redirección `301` aplicada en Rank Math | Mantener la redirección mientras el programa esté retirado | La URL histórica no se recrea como oferta activa. |
| 26 | Resuelta | Tuition Payment Options `/general/tuition-payment-options/` | Tuition Payment Options `/general/tuition-payment-options/` | URL alineada | Ninguno | Slug y jerarquía coinciden. Rediseño local con Design System 0.2 aplicado el 2026-10-02 en Elementor; contenido, enlace al documento y Rank Math conservados. |
| 27 | Resuelta | VISA I-20 Assistance `/general/visa-i-20-assistance/` | VISA I-20 Assistance `/general/visa-i-20-assistance/` | URL alineada | Ninguno | Slug y jerarquía coinciden. Rediseno local con Design System 0.2 aplicado el 2026-10-02 en Elementor; contenido, PDF, enlace SEVP y Rank Math conservados. |
| 28 | Resuelta | LP - Home Page `/lp/` | LP - Home Page `/lp/` | Landing maquetada en Elementor y `noindex` aplicado | Mantener `noindex` | Replica la estructura pública con hero, formulario, programas y llamadas a la acción editables. |
| 29 | Resuelta | LP - Campus Location `/lp/campus-location/` | LP - Campus Location `/lp/campus-location/` | Landing migrada a Elementor y `noindex` aplicado | Mantener `noindex` | Contenido trasladado a widgets editables. |
| 30 | Resuelta | LP - Professional Nursing `/lp/pnp/` | LP - Professional Nursing `/lp/pnp/` | Landing maquetada en Elementor y `noindex` aplicado | Mantener `noindex` | Contenido, imágenes, formulario y secciones reconstruidos. |
| 31 | Resuelta | LP - PTA `/lp/pta/` | LP - PTA `/lp/pta/` | Landing maquetada en Elementor y `noindex` aplicado | Mantener `noindex` | Contenido, imágenes, formulario y secciones reconstruidos. |
| 32 | Resuelta | Privacy Policy `/privacy-policy/` | Privacy Policy `/privacy-policy/` | URL alineada | Ninguno | URL alineada; se retiraron referencias a programas descontinuados. |
| 33 | Resuelta | SABER College Blogs `/saber_college_blog/` | Blog `/saber_college_blog/` | URL alineada | Ninguno | Conserva el archivo de blog existente. |
| 34 | Resuelta | Programs `/saber_college_programs/` | Programs `/saber_college_programs/` | URL alineada | Ninguno | Preserva las rutas anidadas de programas. |
| 35 | Resuelta | PTA Program A.S. `/saber_college_programs/physical-therapist-assistant-program/` | PTA Program `/saber_college_programs/physical-therapist-assistant-program/` | URL alineada | Ninguno | Usar como URL canónica del programa. |
| 36 | Resuelta | Admissions - PTA `/saber_college_programs/physical-therapist-assistant-program/admissions-pta/` | PTA Admissions `/saber_college_programs/physical-therapist-assistant-program/admissions-pta/` | URL alineada | Ninguno | Conserva la ruta publica de admision. Rediseno local con Design System 0.2 aplicado el 2026-10-02 en Elementor; contenido, enlaces y Rank Math conservados. |
| 37 | Resuelta | Nursing Program A.S. `/saber_college_programs/professional-nursing-program/` | Nursing Program `/saber_college_programs/professional-nursing-program/` | URL alineada | Ninguno | Usar como URL canónica del programa. |
| 38 | Resuelta | Admissions - Nursing `/saber_college_programs/professional-nursing-program/nursing-admissions/` | Nursing Admissions `/saber_college_programs/professional-nursing-program/nursing-admissions/` | URL alineada | Ninguno | Conserva la ruta pública de admisión. Rediseño local con Design System 0.2 aplicado el 2026-10-02 en Elementor; contenido, enlaces y Rank Math conservados. |
| 39 | Resuelta | Accreditation `/saber-college-accreditation/` | Accreditation `/saber-college-accreditation/` | URL alineada; declaración y sello NLN CNEA agregados | Ninguno | Se conservaron COE, CIE y CAPTE; el sello oficial NLN CNEA se incorporó el 2026-10-05. |
| 40 | Resuelta | Student Services `/student-services/` | Student Services `/student-services/` | URL alineada | Ninguno | URL ya coincide. Rediseño local con Design System 0.2 aplicado el 2026-10-02 en Elementor; textos, 11 enlaces y Rank Math conservados. |
| 41 | Resuelta | Academic Affairs `/student-services/academic-affairs/` | Academic Affairs `/student-services/academic-affairs/` | URL alineada | Ninguno | Texto SEO suministrado por Marketing aplicado íntegramente el 2026-10-05. |
| 42 | Resuelta | Career Services `/student-services/career-services/` | Career Services `/student-services/career-services/` | URL alineada | Ninguno | Texto SEO suministrado por Marketing aplicado íntegramente el 2026-10-05. |
| 43 | Resuelta | Saber Survey `/student-services/saber-survey/` | SABER Survey `/student-services/saber-survey/` | URL alineada | Ninguno | URL ya coincide. Rediseno local con Design System 0.2 aplicado el 2026-10-02 en Elementor; contenido, formulario y Rank Math conservados. |
| 44 | Resuelta | Student Transcript Request `/student-services/student-transcript-request/` | Student Transcript Request `/student-services/student-transcript-request/` | URL alineada | Ninguno | URL ya coincide. Rediseno local con Design System 0.2 aplicado el 2026-10-02 en Elementor; contenido, formulario PDF, correos y Rank Math conservados. |
| 45 | Resuelta | Terms & Conditions `/terms-conditions/` | Terms & Conditions `/terms-conditions/` | URL alineada | Ninguno | URL ya coincide. |
| 46 | Resuelta | Thank You Page `/thank-you/` | Thank You Page `/thank-you/` | URL alineada | Revisar `noindex` al cerrar el flujo | Página creada y separada de `/lp/thank-you/`. |
| 47 | Interna | No existe en producción | SABER Design System – Elementor `/saber-design-system-elementor/` | Biblioteca de componentes publicada con `noindex` | No migrar como contenido público definitivo | Fuente reutilizable para construir páginas en Elementor; ocultar o despublicar al cerrar el proyecto. |

## Páginas locales sin equivalente público directo

| Página local | URL local actual | Recomendación | Comentario |
| --- | --- | --- | --- |
| Admissions and Tuition | `/admissions-and-tuition/` | Mantener como página nueva pública | No tiene equivalente público actual y se conservará como parte de la nueva estructura. |
| Plan Bunji | `/plan-bunji/` | `noindex` aplicado; eliminar tras la entrega del proyecto | No se indexará mientras permanezca disponible localmente. |
| Request Info | `/request-info/` | Mantener como página de conversión | Se enriquecerá con contenido más adelante; no se modifica su indexación por ahora. |

## Control de indexación de landings

| URL local | Estado Rank Math |
| --- | --- |
| `/lp/` | `noindex` |
| `/lp/campus-location/` | `noindex` |
| `/lp/pnp/` | `noindex` |
| `/lp/pta/` | `noindex` |
| `/lp/vesl/` | Página en papelera desde 2026-10-05; destino SEO pendiente |
| `/lp/medical-assistant-program/` | Página en papelera desde 2026-10-05; destino SEO pendiente |
| `/lp/thank-you/` | Página en papelera; `301` hacia `/thank-you/` |

## Migración de documentos PDF

- Se importaron los 200 PDF registrados en el inventario público a la biblioteca multimedia local.
- Los archivos conservan su nombre original y la estructura histórica de carpetas por año y mes.
- Se actualizaron 40 contenidos de WordPress y 233 registros de metadatos/Elementor que enlazaban directamente a PDF del dominio público.
- La validación final no encontró URLs externas de PDF de `sabercollege.edu` en contenidos ni metadatos locales.
- Los 200 adjuntos PDF tienen archivo físico disponible y un mapeo único hacia su URL pública de origen.
- La página `/general/heerf-quarterly-reports/` enlaza **HEERF Quarterly Report ending 9-30-20** al PDF local canónico, sin crear un adjunto duplicado.
- Permanecen sin importar tres documentos Word porque esta acción se limitó a PDF; están identificados en [archive-compilado.md](archive-compilado.md).

## Pendientes de redirección y revisión

Quedan pendientes las decisiones de destino para VESL/ESOL, Medical Assisting, sus artículos y sus taxonomías. Como no se suministraron páginas equivalentes, se desactivaron las reglas que terminaban en contenido retirado. Antes de migrar debe definirse por URL si corresponde una redirección temática real o una respuesta `410 Gone`; no se recomienda enviarlas todas al Home.

## Portabilidad de enlaces internos

- Los enlaces activos que apuntaban de forma absoluta a `sabercollege.edu` se normalizaron como rutas relativas para funcionar en local, staging y producción.
- Se conservaron las URLs absolutas de los inventarios Markdown y los metadatos técnicos de procedencia de medios importados; los enlaces internos de las revisiones también quedaron normalizados.
