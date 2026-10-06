# SABER College Website Profile

**Fecha de revision:** 1 de octubre de 2026  
**Sitio publico revisado:** [https://sabercollege.edu/](https://sabercollege.edu/)  
**Proyecto local revisado:** `http://localhost:8082/`

## Alcance del documento

Este perfil consolida la identidad institucional, la oferta academica, la experiencia digital y la direccion visual observadas en el sitio publico y en el nuevo WordPress local de SABER College.

El documento sirve como fuente de contexto para el rediseño, la migracion y la futura creacion de un design system. No sustituye el catalogo institucional vigente, los documentos regulatorios, un manual oficial de marca ni la validacion juridica de afirmaciones sobre acreditacion, resultados, costos o empleabilidad.

Cuando existe una diferencia entre contenido historico y decisiones actuales del proyecto, se documenta expresamente.

## Resumen general

SABER College es una institucion privada, sin fines de lucro, ubicada en Miami, Florida, y orientada a la educacion profesional y al desarrollo de oportunidades laborales. Su propuesta actual se concentra en dos programas de Associate of Science dentro del sector salud:

- Professional Nursing Program (A.S.)
- Physical Therapist Assistant Program (A.S.)

La institucion comunica una experiencia educativa cercana, multicultural y enfocada en el estudiante. Combina instruccion teorica, laboratorios, experiencia clinica, acompañamiento academico, servicios de carrera y orientacion durante el proceso de admision y financiamiento.

El mensaje central que emerge del nuevo sitio es que SABER College ayuda a transformar el interes por servir a otros en una carrera sanitaria mediante educacion practica, apoyo personal y una ruta clara desde la admision hasta la graduacion.

## Identidad institucional

- **Nombre publico:** SABER College
- **Entidad legal:** Spanish American Basic Education and Rehabilitation, Inc. (SABER), doing business as SABER College
- **Naturaleza:** corporacion privada sin fines de lucro reconocida como organizacion `501(c)(3)`
- **Fundacion:** 1972
- **Ubicacion principal:** 3990 West Flagler Street, Suite 103, Miami, Florida 33134
- **Telefono principal:** (305) 443-9170
- **Correo institucional general:** saber@sabercollege.edu
- **Sitio web:** [https://sabercollege.edu/](https://sabercollege.edu/)
- **Sector principal:** educacion superior orientada a carreras de salud
- **Idioma de los programas de salud:** ingles, con apoyo bilingue visible en diferentes puntos de contacto

## Esencia de marca

### Proposito percibido

Ofrecer educacion accesible y orientada al trabajo que ayude a los estudiantes a desarrollar conocimientos, habilidades, responsabilidad etica y confianza para integrarse al sector salud y mejorar su calidad de vida.

### Promesa de marca

SABER College acompaña al estudiante con educacion practica, atencion personalizada y apoyo durante las etapas que van desde la admision hasta la preparacion profesional.

### Personalidad

- Cercana y acogedora
- Profesional y responsable
- Optimista y orientada al progreso
- Multicultural y vinculada a Miami
- Practica, humana y centrada en el estudiante
- Institucional, pero sin la distancia de una universidad de gran escala

### Arquetipos predominantes

- **Cuidador:** la oferta academica prepara profesionales que ayudan a otros y recuperan o protegen la salud.
- **Guia:** el sitio insiste en el acompañamiento durante admisiones, ayuda financiera, vida academica y desarrollo profesional.
- **Explorador / crecimiento:** el lenguaje de marca invita a encontrar un camino, descubrir capacidades y avanzar hacia una nueva carrera.

### Diferenciadores percibidos

- Historia institucional en Miami desde 1972.
- Atencion personal y enfoque `student first`.
- Programas concentrados en profesiones sanitarias.
- Formacion practica mediante laboratorio y experiencias clinicas.
- Docentes con experiencia profesional.
- Apoyo de admisiones, asesoria academica, ayuda financiera y career services.
- Identidad local y relacion con una comunidad culturalmente diversa.
- Condicion de institucion privada sin fines de lucro.

## Identidad visual y de marca

Esta seccion documenta lo implementado en el WordPress local y los patrones observados en el sitio. Los valores no deben considerarse un brand book oficial hasta ser validados contra archivos maestros de la institucion.

### Logotipo y firma

- El logo utiliza una forma triangular o de escudo inclinado que combina el nombre `SABER College` con trazos dinamicos.
- La firma transmite movimiento, progreso y una identidad educativa contemporanea.
- La version utilizada en el proyecto incorpora el descriptor `Curious Minds`.
- El logotipo funciona con especial claridad sobre fondos blancos o azul muy oscuro.
- Debe conservarse su proporcion y evitar colocarlo sobre zonas fotograficas con demasiado detalle.
- Para mobile debe reservarse espacio superior suficiente para que breadcrumbs, titulos o menus no interfieran con el logo.

### Mascota

`Sabi` funciona como activo de comunidad y expresion de pertenencia. Su uso es adecuado para:

- vida estudiantil
- bienvenida y orientacion
- eventos y celebraciones
- contenido social
- publicaciones editoriales sobre comunidad

No debe sustituir el sello institucional en acreditacion, politicas, documentos regulatorios o comunicaciones formales.

### Paleta cromatica implementada

Los colores globales actuales de Elementor son:

| Rol | Color | Valor | Aplicacion recomendada |
| --- | --- | --- | --- |
| Primary | Azul noche | `#001A39` | Header, footer, fondos institucionales y alto contraste. |
| Secondary | Azul medio | `#25377B` | Variaciones de marca, elementos secundarios y profundidad. |
| Accent | Amarillo SABER | `#FDC800` | CTA, indicadores, detalles y puntos de energia. |
| Text | Gris | `#7A7A7A` | Token heredado; debe revisarse antes de usarlo en parrafos por contraste. |
| Enlace | Azul digital | `#0070E0` | Enlaces y breadcrumbs. |

En las paginas rediseñadas tambien se esta usando una paleta funcional complementaria:

| Rol | Color | Valor | Uso actual |
| --- | --- | --- | --- |
| Azul editorial | Azul SABER profundo | `#082B55` | Heroes, botones secundarios, cierres y bloques de confianza. |
| Texto principal | Azul grisaceo | `#173653` / `#29435F` | Parrafos sobre fondos claros. |
| Superficie | Gris azulado claro | `#F3F7FA` | Alternancia de secciones y fondos de lectura. |
| Superficie calida | Amarillo muy claro | `#FFF8D8` | Tarjetas destacadas sin perder legibilidad. |
| Blanco | Blanco | `#FFFFFF` | Tarjetas, formularios y espacios de respiracion. |

Notas de aplicacion:

- El azul debe comunicar confianza, estructura y credibilidad institucional.
- El amarillo debe reservarse para llamadas a la accion, datos clave y momentos de optimismo.
- Los fondos claros y calidos deben tener mayor presencia para que el sitio no se convierta en una sucesion de bloques azul oscuro.
- El texto largo debe presentarse en azul oscuro sobre blanco o fondos muy claros.
- El amarillo sobre blanco no debe utilizarse para texto pequeño sin comprobar contraste.

### Tipografia

- **Familia principal actual:** `Nunito`
- **Uso:** titulares, cuerpo, botones, formularios y navegacion
- **Pesos predominantes:** 400, 500, 600, 700 y 800
- **Respaldo global actual:** `Sans-serif`

Jerarquia recomendada a partir de lo ya implementado:

- Hero H1: Nunito 800, aproximadamente 52-60 px en desktop y 40-42 px en mobile.
- H2: Nunito 800, aproximadamente 38-44 px en desktop y 30-34 px en mobile.
- H3: Nunito 800, aproximadamente 23-28 px.
- Eyebrow o texto introductorio: Nunito 700-800, 17-20 px.
- Cuerpo: Nunito 400-500, 16-18 px, con interlineado amplio.
- Botones: Nunito 700-800, 15-17 px.
- Breadcrumbs: aproximadamente 13 px, centrados y con suficiente separacion del header.

### Direccion grafica y fotografia

La home demuestra que la identidad gana riqueza cuando combina composicion, fotografia y superficies claras. La direccion futura debe priorizar:

- Fotografias reales de estudiantes, docentes, laboratorios y experiencias clinicas.
- Escenas de aprendizaje y colaboracion, no fotografias genericas de salud sin relacion visible con SABER.
- Retratos humanos con espacio negativo suficiente para titulares o CTA.
- Recortes organicos, diagonales suaves y detalles lineales que dialoguen con la geometria del logo.
- Fondos claros con formas sutiles, degradados muy suaves o bloques de color calido.
- Uso medido de fondos azul oscuro para heroes, acreditacion, conversion y cierres de alto impacto.
- Imagenes con proporciones consistentes por componente para evitar saltos visuales.

### Tono de voz

- **Cercano:** habla directamente al futuro estudiante y reconoce que elegir una carrera es una decision importante.
- **Orientado al acompañamiento:** repite que el estudiante no tiene que recorrer el proceso solo.
- **Aspiracional, pero realista:** presenta posibilidades profesionales sin prometer empleo o resultados garantizados.
- **Claro:** explica programas, requisitos y siguientes pasos con lenguaje accesible.
- **Humano:** conecta la educacion sanitaria con ayudar a personas y mejorar vidas.
- **Institucional:** en acreditacion, politicas y documentos debe ser preciso, verificable y sobrio.
- **Bilingue cuando aporta valor:** el ecosistema editorial y la comunidad incluyen contenido en ingles y español.

Verbos y conceptos alineados con la marca:

- learn
- prepare
- support
- care
- begin
- advance
- build confidence
- take the next step
- hands-on training
- student success

## Mision, filosofia y enfoque

La mision publicada identifica como responsabilidad principal preparar individuos para convertirse en miembros productivos de la sociedad, ayudandolos a alcanzar metas educativas y desarrollar responsabilidades eticas.

La filosofia institucional presenta a SABER College como un vehiculo para:

- desarrollar habilidades basicas y profesionales
- impartir educacion vocacional y de carrera
- responder a ocupaciones de alta demanda
- contribuir al crecimiento economico de la comunidad
- facilitar mejores oportunidades profesionales
- mejorar la calidad de vida de los estudiantes

El sitio local traduce esta mision en tres ideas recurrentes:

1. Educacion sanitaria practica y enfocada en la carrera.
2. Acompañamiento personal durante toda la experiencia educativa.
3. Preparacion para actuar con competencia y responsabilidad en entornos reales.

## Historia y contexto institucional

- Spanish American Basic Education & Rehabilitation, Inc. fue establecida en Florida en 1972.
- La institucion surgio para responder a necesidades de conocimientos, habilidades y personal preparado en distintas areas ocupacionales.
- SABER College amplio posteriormente su actividad hacia programas vocacionales, no conducentes a titulo y conducentes a grado.
- El contenido institucional indica que cuenta con licencia de la Commission for Independent Education desde 1991.
- La institucion mantiene una relacion historica con el desarrollo laboral y educativo de la comunidad de Miami.

## Oferta academica activa

El rediseño debe presentar con claridad que la oferta academica activa se concentra en dos programas. Esta especializacion es una fortaleza: permite crear recorridos mas directos y detallados que los de una universidad con decenas de carreras.

### 1. Professional Nursing Program (A.S.)

Propuesta:

- Preparar graduados para aplicar el proceso de enfermeria y brindar cuidado a personas a lo largo de su vida y en diferentes contextos.
- Integrar teoria, laboratorio y rotaciones clinicas.
- Desarrollar conocimientos en medical/surgical nursing, obstetric-gynecologic nursing, pediatric nursing, gerontologic nursing y psychiatric nursing.

Datos publicados en el contenido local:

- 80 credit hours
- 1,725 clock hours
- Programa intensivo y full-time
- Associate of Science Degree in Nursing
- Preparacion vinculada al comprehensive exit exam y a la ruta hacia NCLEX-RN

Temas que la pagina de programa debe explicar siempre:

- descripcion de la profesion
- estructura y duracion
- plan de estudios
- practica clinica
- requisitos de admision
- licensure y examenes
- acreditacion especifica
- costos y ayuda financiera
- resultados y fuentes con fecha
- preguntas frecuentes

### 2. Physical Therapist Assistant Program (A.S.)

Propuesta:

- Preparar al estudiante para trabajar bajo la direccion y supervision de un physical therapist.
- Combinar teoria, habilidades tecnicas y educacion clinica.
- Desarrollar competencias en anatomy, kinesiology, pathophysiology, patient data collection, therapeutic interventions y patient care.

Datos y caracteristicas visibles:

- 1,785 clock hours indicadas en las paginas generales del proyecto
- Associate of Science Degree
- Experiencias clinicas integradas en el curriculum
- Elegibilidad para presentar el National Physical Therapy Examination (NPTE) para PTA despues de completar el programa, sujeto a los requisitos aplicables
- Programa orientado a competencia de nivel inicial bajo supervision profesional

Temas que la pagina debe priorizar:

- funciones reales de un PTA
- practica y laboratorios
- clinical experiences
- requisitos y observation hours
- admission capacity y proceso competitivo
- NPTE y requisitos regulatorios
- acreditacion CAPTE
- carrera y datos ocupacionales actualizados

## Programas historicos o retirados

Medical Assisting y ESOL/VESL dejaron de formar parte de la oferta que se desea promover. Sin embargo, el sitio publico conserva paginas y contenido historico relacionado.

Tratamiento actual en el proyecto:

- Las landing pages dentro de `/lp/` se mantienen en `noindex`.
- `/medical-assistant-program/` redirige a `/lp/medical-assistant-program/` mientras se conserva el material historico.
- `/esol-program/` redirige a `/lp/vesl/`.
- `/programa-vesl-online/` redirige a la pagina general de programas.
- Estas paginas no deben aparecer como oferta activa en navegacion, cards principales o mensajes institucionales nuevos.
- Antes de publicar afirmaciones sobre VESL o Medical Assisting se debe confirmar su vigencia con SABER College.

## Admisiones

El nuevo contenido de admisiones propone un recorrido sencillo y acompañado:

1. Explorar el programa.
2. Revisar los requisitos especificos.
3. Conectar con el equipo de admisiones.
4. Aplicar y prepararse para enrollment.

Las rutas de Professional Nursing y PTA tienen requisitos propios y paginas anidadas. La experiencia digital debe evitar mezclar requisitos generales con requisitos programaticos.

Principios para presentar admisiones:

- Mostrar primero una vista resumida del proceso.
- Separar claramente Nursing y PTA.
- Presentar requisitos en listas escaneables.
- Identificar documentos, entrevistas, examenes, observation hours, background checks y health requirements donde correspondan.
- No insinuar que cumplir requisitos minimos garantiza la aceptacion.
- Mantener siempre una via visible para hablar con una persona del equipo.

## Tuition, ayuda financiera y pagos

El sitio comunica que la educacion es una inversion y que el equipo puede orientar al estudiante para entender tuition, payment options y financial assistance disponible para quienes califiquen.

Elementos del ecosistema actual:

- Net Price Calculator
- Tuition Payment Options
- Financial Aid guidance
- TFC Tuition Funding
- Federal Student Aid resources
- Annual Student Loan Acknowledgment
- Informacion sobre Title IV eligibility

Reglas de comunicacion:

- Evitar prometer que una persona recibira ayuda financiera.
- Usar siempre expresiones como `available to those who qualify` cuando correspondan.
- Mantener costos, fechas y condiciones vinculados a documentos vigentes.
- Diferenciar claramente tuition, fees, financing, federal aid y payment plans.
- Facilitar el acceso a asesores sin ocultar informacion basica detras de un formulario.

## Acreditacion, licencias y aprobaciones

El sitio local presenta los siguientes elementos institucionales:

- Licencia de la Florida Department of Education, Commission for Independent Education (CIE).
- Acreditacion institucional del Council on Occupational Education (COE).
- Acreditacion programatica del Physical Therapist Assistant Program por la Commission on Accreditation in Physical Therapy Education (CAPTE).
- El contenido del Professional Nursing Program indica acreditacion inicial por la National League for Nursing Commission for Nursing Education Accreditation (NLN CNEA).
- Elegibilidad institucional para participar en programas Title IV del U.S. Department of Education, segun la pagina de acreditacion.
- Aprobacion para prestar servicios mediante programas de Vocational Rehabilitation.

Toda afirmacion regulatoria debe:

- utilizar el nombre oficial completo de la entidad
- indicar correctamente si se trata de licencia, acreditacion institucional, acreditacion programatica, aprobacion o elegibilidad
- enlazar a la fuente oficial o al documento vigente
- incluir fecha cuando el estado pueda cambiar
- revisarse antes de una campaña o migracion importante

Los logotipos de acreditacion no deben presentarse solo como decoracion; conviene acompañarlos con contexto comprensible para el estudiante.

## Campus e instalaciones

La sede principal publicada es:

**3990 West Flagler Street, Suite 103, Miami, Florida 33134.**

El recorrido digital de campus debe facilitar:

- ubicacion y mapa
- telefono y correo
- solicitud de informacion
- programacion de visita
- galeria de instalaciones
- acceso, estacionamiento y referencias utiles si se validan institucionalmente
- relacion entre laboratorios, aulas y el aprendizaje practico

La fotografia de campus representa una oportunidad importante para diferenciar la marca y reducir la dependencia de imagenes genericas.

## Liderazgo y comunidad

La pagina de liderazgo posiciona al equipo como garante de:

- direccion estrategica
- integridad academica
- cumplimiento institucional
- coordinacion de programas y experiencias clinicas
- admisiones y ayuda financiera
- servicios estudiantiles
- colocacion y preparacion profesional

El perfil publico incluye funciones ejecutivas, academicas, administrativas, de distancia, acreditacion, biblioteca y placement. Los nombres y cargos deben revisarse periodicamente porque son datos susceptibles de cambio.

## Servicios para estudiantes

La arquitectura local incluye:

- Academic Affairs
- Career Services
- Student Transcript Request
- VISA I-20 Assistance
- SABER Survey
- recursos de pagos y student loans
- referencias de apoyo externo
- voter registration information

Career Services se presenta como apoyo para resume, cover letter, entrevistas y busqueda de oportunidades. La comunicacion debe aclarar que se brinda asistencia y no una garantia de empleo.

## Experiencia digital y conversion

El sitio tiene una orientacion clara a futuros estudiantes. Las acciones principales son:

- Request Information
- Apply to SABER College
- Explore Programs
- View Admissions Requirements
- Contact Admissions
- Schedule or request a campus visit

### Recorrido principal recomendado

```text
Home
-> Programs
-> Program Detail
-> Admissions Requirements
-> Tuition / Financial Aid
-> Request Information or Apply
-> Thank You / Next Step
```

### Patrones de interfaz que ya funcionan

- Header con informacion de contacto y navegacion principal.
- Hero con propuesta de valor, fotografia y CTA.
- Tarjetas para comparar programas.
- Secciones alternadas con fondos claros, blanco, amarillo suave y azul institucional.
- Bloques de pasos para procesos de admision.
- Tarjetas de documentos y recursos descargables.
- CTA final de alto contraste.
- Breadcrumbs centrados en paginas internas.
- Testimonios, reseñas y contenido de comunidad como prueba social.

### Riesgos actuales de conversion

- Algunos botones del contenido local todavia no tienen URL configurada.
- Existen secciones historicas con rutas duplicadas o redirigidas.
- Los CTA pueden variar de etiqueta y destino entre paginas.
- La riqueza visual no es uniforme: la home combina imagenes y fondos claros, mientras varias paginas internas dependen principalmente de tarjetas de color.
- Formularios, consentimientos y paginas de agradecimiento deben comportarse de forma consistente.

## Arquitectura de informacion

Las areas principales del nuevo sitio son:

- Home
- Programs
- Admissions & Tuition
- Student Services
- General Overview
- About Us
- Blog
- Request Information
- Apply to SABER College

La estructura debe mantener separadas cuatro necesidades:

1. Descubrir y comparar programas.
2. Entender requisitos, costos y proceso de ingreso.
3. Acceder a servicios como estudiante actual.
4. Consultar informacion institucional, regulatoria y documental.

Las landing pages de campaña bajo `/lp/` no deben definir la arquitectura principal del sitio y actualmente permanecen en `noindex`.

## Blog y contenido organico

El WordPress local contiene 86 entradas publicadas. La estrategia editorial cubre:

- Professional Nursing
- Physical Therapist
- Healthcare Programs
- Career Resources
- Higher Education
- Financial Aid
- College News
- Sabi y comunidad
- contenido historico de Medical Assisting y VESL

La produccion incluye contenido en ingles y español, lo que refleja la comunidad de Miami y permite construir rutas organicas bilingues.

Temas con mayor afinidad futura:

- explicacion de carreras y funciones profesionales
- preparacion para NCLEX-RN y NPTE
- experiencia clinica y laboratorios
- admisiones y financiamiento
- demanda ocupacional con fuentes actualizadas
- historias de estudiantes y graduados
- facultad, liderazgo y comunidad
- vida estudiantil y Sabi

Las categorias deben ayudar a descubrir contenido. Las etiquetas historicas permanecen fuera de indexacion para evitar archivos de bajo valor y duplicacion.

## Estado del proyecto digital

Al 1 de octubre de 2026, el WordPress local registra:

- 44 paginas publicadas
- 86 entradas publicadas
- 376 elementos en la biblioteca multimedia
- 36 paginas y 86 entradas previstas como indexables segun la auditoria SEO documentada
- 8 paginas con `noindex, follow`, correspondientes a conversion o landings controladas

El proyecto ya cuenta con inventarios separados de:

- paginas publicas y locales
- blogs publicos y locales
- archivos y documentos
- comparativos de URLs
- redirecciones
- plan de trabajo SEO
- referentes visuales del sector

## SEO y migracion

El objetivo SEO principal es reemplazar el sitio publico sin perder indexacion, autoridad ni continuidad de URLs.

Trabajo ya documentado:

- URLs equivalentes comparadas y alineadas.
- Redirecciones historicas configuradas con Rank Math.
- Metadatos de paginas y entradas completados.
- Canonicals automaticos preparados para adoptar el dominio final.
- Schema `WebPage` y `BlogPosting` aplicado segun tipo de contenido.
- `/lp/`, landings, Thank You y Plan Bunji controlados mediante `noindex`.
- Categorias historicas relevantes restauradas.
- PDFs publicos importados a la biblioteca local.
- Enlaces internos absolutos del dominio publico normalizados a rutas relativas.
- Sitemaps y reglas de indexacion revisados en local.

Validaciones necesarias al migrar:

- dominio y HTTPS
- canonicals finales
- robots.txt
- sitemap de Rank Math
- redirecciones 301
- formularios y correos
- recursos mixtos o rotos
- Search Console y analytics
- Core Web Vitals
- rastreo integral de paginas, entradas, categorias y documentos

## Tecnologia del proyecto local

- WordPress
- Docker para el entorno local reproducible
- Tema Hello Elementor
- Elementor
- Pro Elements
- Rank Math SEO
- Ninja Tables
- Google Reviews plugin
- All-in-One WP Migration para tareas de portabilidad

El sitio utiliza Elementor como capa principal de edicion visual. Por esa razon, el futuro design system debe traducirse a:

- Global Colors
- Global Fonts
- clases reutilizables
- containers y patrones de layout
- templates de seccion y pagina
- variables CSS del tema hijo o capa de personalizacion
- reglas responsive compartidas

## Evaluacion UX/UI actual

### Fortalezas

- Identidad cromatica reconocible.
- Tipografia amable y coherente.
- Home con fondos claros, imagenes y una narrativa mas rica.
- Oferta compacta que permite recorridos simples.
- Mensaje humano y enfocado en acompañamiento.
- Contenido institucional amplio y buen inventario documental.
- Buen potencial para fotografia autentica y storytelling local.
- Base Elementor que permite edicion por el equipo.

### Debilidades

- Las paginas internas no siempre alcanzan la riqueza visual de la home.
- Se repiten tarjetas azules, amarillas y blancas sin suficientes variaciones de composicion.
- No existe todavia una regla documentada para imagenes, heroes, espaciado o fondos.
- Algunos contenidos historicos mezclan programas activos y retirados.
- Existen botones sin destino y CTA con etiquetas variables.
- Algunos datos ocupacionales o regulatorios requieren actualizacion y fuente visible.
- La cantidad de contenido en ciertas paginas puede dificultar la lectura si no se divide en capas.

### Oportunidades

- Convertir la historia desde 1972 en una señal visible de confianza.
- Diferenciar los dos programas con sistemas fotograficos y narrativos propios.
- Mostrar laboratorios, practica clinica, docentes y campus de forma mas autentica.
- Construir paginas de programa consistentes con datos clave visibles.
- Integrar admisiones, tuition y ayuda financiera en un recorrido continuo.
- Aprovechar el contenido bilingue con una estrategia definida, no solo entradas aisladas.
- Usar Sabi para comunidad sin restar formalidad a la informacion academica.

## Implicaciones para el futuro design system

El design system de SABER College debe resolver, como minimo:

1. Tokens oficiales de color y contraste.
2. Escala tipografica para desktop, tablet y mobile.
3. Reglas de espaciado, ancho y ritmo vertical.
4. Tratamiento de fotografia, proporciones y recortes.
5. Heroes para home, programas, paginas institucionales y conversion.
6. Botones primarios, secundarios, text links y estados interactivos.
7. Tarjetas de programas, pasos, documentos, perfiles, noticias y datos.
8. Formularios y mensajes de confirmacion.
9. Acordeones, tablas y contenido regulatorio.
10. Header, breadcrumbs, navegacion mobile y footer.
11. Accesibilidad, foco, contraste y lectura por teclado.
12. Componentes nativos de Elementor que permanezcan editables.

La meta no es que todas las paginas se vean iguales. El sistema debe asegurar consistencia y, al mismo tiempo, permitir que cada tipo de pagina use imagenes, composiciones y niveles de energia adecuados a su funcion.

## Principios recomendados para el rediseño

- Diseñar para decisiones del estudiante, no para reflejar la estructura administrativa interna.
- Presentar primero la informacion esencial y permitir profundizar despues.
- Usar fotografia autentica como parte del contenido, no como decoracion aislada.
- Reservar el amarillo para acciones y momentos de enfasis.
- Alternar fondos claros para mejorar ritmo y reducir fatiga visual.
- Mantener una accion principal y una secundaria por seccion.
- No publicar cifras, resultados o estados regulatorios sin fuente y fecha.
- No promover programas retirados como parte de la oferta activa.
- Mantener todos los componentes editables desde Elementor.
- Validar cada pagina en desktop, tablet y mobile antes de publicarla.

## Observaciones importantes

### 1. Oferta activa frente a contenido historico

El ecosistema todavia contiene referencias a Medical Assisting y VESL/ESOL. El nuevo sitio debe presentar Professional Nursing y PTA como oferta activa, mientras los contenidos historicos se conservan, redirigen o retiran segun su valor SEO y regulatorio.

### 2. Acreditacion y cifras requieren control editorial

Los nombres de acreditadores, estados, horas, creditos, salarios y proyecciones pueden cambiar. Antes del lanzamiento deben compararse con el catalogo vigente y las fuentes oficiales.

### 3. La home ya señala la direccion visual correcta

La combinacion de fondos claros, fotografia, azul institucional, amarillo y composiciones amplias produce una experiencia mas rica que la repeticion de tarjetas. Esta direccion debe convertirse en reglas compartidas y no depender de decisiones aisladas por pagina.

### 4. La propuesta de valor mas fuerte de SABER

La principal oportunidad no es parecer una universidad grande. Es comunicar con claridad una institucion cercana, con historia en Miami, dos rutas sanitarias especializadas, practica real y acompañamiento personal.

## Fuentes revisadas

### Sitio y paginas institucionales

- [SABER College](https://sabercollege.edu/)
- [About Us](https://sabercollege.edu/about_saber_college/)
- [Programs](https://sabercollege.edu/saber_college_programs/)
- [Professional Nursing Program](https://sabercollege.edu/saber_college_programs/professional-nursing-program/)
- [Physical Therapist Assistant Program](https://sabercollege.edu/saber_college_programs/physical-therapist-assistant-program/)
- [Nursing Admissions](https://sabercollege.edu/saber_college_programs/professional-nursing-program/nursing-admissions/)
- [PTA Admissions](https://sabercollege.edu/saber_college_programs/physical-therapist-assistant-program/admissions-pta/)
- [Accreditation](https://sabercollege.edu/saber-college-accreditation/)
- [Campus Location](https://sabercollege.edu/campus-location/)
- [Student Services](https://sabercollege.edu/student-services/)
- [General Overview](https://sabercollege.edu/general/)
- [Blog](https://sabercollege.edu/saber_college_blog/)

### Documentacion del proyecto

- [pages.md](pages.md)
- [pages-new.md](pages-new.md)
- [pages-compilado.md](pages-compilado.md)
- [blogs.md](blogs.md)
- [blogs-new.md](blogs-new.md)
- [blogs-compilado.md](blogs-compilado.md)
- [archive.md](archive.md)
- [archive-new.md](archive-new.md)
- [archive-compilado.md](archive-compilado.md)
- [seo.md](seo.md)
- [referentes/referentes.md](referentes/referentes.md)

## Mantenimiento del perfil

Actualizar este archivo cuando ocurra alguno de estos cambios:

- alta o retiro de un programa
- cambio de campus, telefono o correo institucional
- cambio de acreditacion, licencia o aprobacion
- publicacion de un nuevo catalogo
- cambio de liderazgo
- aprobacion de un brand book
- implementacion del design system
- migracion definitiva al nuevo WordPress
- cambio relevante en arquitectura, conversion o estrategia SEO
