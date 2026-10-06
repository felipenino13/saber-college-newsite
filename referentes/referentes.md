# Referentes web para el rediseño de SABER College

**Fecha de revisión:** 1 de octubre de 2026  
**Objetivo:** reunir referentes del sector de educación superior en salud que ayuden a mejorar la experiencia de usuario, la arquitectura de información, la presentación de programas y la conversión del sitio de SABER College.

> Este es un ranking de referencia para diseño web, no una clasificación académica. El orden combina afinidad con la oferta de SABER College, calidad percibida de UX/UI, claridad del proceso de admisión y utilidad práctica para el futuro rediseño.

## Criterios de selección

- Instituciones especializadas o reconocidas en enfermería, terapia física, rehabilitación y ciencias de la salud.
- Sitios que permiten encontrar programas y requisitos sin depender de una navegación compleja.
- Buen uso de llamadas a la acción como `Request Information`, `Apply` y visitas al campus.
- Presentación clara de duración, modalidad, ubicación, admisiones, acreditación y resultados.
- Diseño visual consistente, accesible y adaptable a dispositivos móviles.
- Inclusión de referentes cercanos al mercado de Florida y de modelos orientados a carreras profesionales.

## Biblioteca visual de capturas

El 1 de octubre de 2026 se creó una biblioteca de **35 capturas de página completa en escritorio**. Las imágenes están guardadas en `referentes/img/`, organizadas según los tipos de contenido que existen o serán necesarios en SABER College.

### Acciones realizadas

1. Se seleccionaron páginas oficiales de los referentes que aportan patrones útiles para SABER College.
2. Se agruparon según la función que cumplen dentro del sitio, no únicamente según la institución de origen.
3. Se capturaron como páginas completas a un ancho de referencia de `1440 px`, en modo claro y formato JPEG.
4. Se revisó visualmente una muestra de cada categoría para detectar páginas vacías, errores o verificaciones de seguridad.
5. Se eliminaron las capturas que solo mostraban bloqueos anti-bot; estos casos permanecen registrados como pendientes en el manifiesto.
6. Se creó un script para poder actualizar la biblioteca cuando cambien los sitios de referencia.
7. Se generó un manifiesto con URL original, URL final, archivo, fecha, estado HTTP y resultado de cada intento.

### Estructura

```text
referentes/
|-- referentes.md
`-- img/
    |-- home/                 # 8 capturas
    |-- programas/            # 6 capturas
    |-- admisiones/           # 6 capturas
    |-- blog/                 # 3 capturas
    |-- articles/             # 3 capturas
    |-- manuales-recursos/    # 2 capturas
    |-- legales/              # 2 capturas
    |-- conversion/           # 2 capturas
    |-- nosotros-campus/      # 3 capturas
    `-- manifest.json
```

### Índice por categoría

| Categoría | Propósito para SABER | Referencias visuales destacadas |
|---|---|---|
| Home | Comparar propuesta de valor, navegación, programas destacados, prueba social y CTA. | [AdventHealth](img/home/adventhealth-university.jpg) · [Johns Hopkins](img/home/johns-hopkins-nursing.jpg) · [University of Miami](img/home/miami-sonhs.jpg) · [West Coast](img/home/west-coast-university.jpg) |
| Programas | Definir el listado de programas y una plantilla consistente para cada carrera. | [AdventHealth Programs](img/programas/adventhealth-programs.jpg) · [AHU BS Nursing](img/programas/ahu-bs-nursing.jpg) · [Chamberlain Nursing](img/programas/chamberlain-nursing-programs.jpg) · [MGBU Academics](img/programas/mgbu-academics.jpg) |
| Admisiones | Ordenar requisitos, fechas, costos, ayuda financiera y pasos de aplicación. | [AdventHealth](img/admisiones/adventhealth-admissions.jpg) · [AHU BSN](img/admisiones/ahu-bsn-admissions.jpg) · [Duke](img/admisiones/duke-admissions.jpg) · [Penn](img/admisiones/penn-admissions.jpg) |
| Blog | Comparar grillas, filtros, categorías, imágenes, paginación y jerarquía editorial. | [AdventHealth](img/blog/ahu-blog.jpg) · [Chamberlain](img/blog/chamberlain-blog.jpg) · [Johns Hopkins Magazine](img/blog/johns-hopkins-magazine.jpg) |
| Articles | Diseñar una plantilla de entrada con autor, fecha, imagen, lectura, enlaces internos y CTA. | [Johns Hopkins Student Profile](img/articles/jhu-outside-track.jpg) · [Chamberlain Alumni Story](img/articles/chamberlain-alumni-story.jpg) · [AHU Accreditation Story](img/articles/ahu-msn-accreditation.jpg) |
| Manuales y recursos | Mejorar la presentación de catálogos, handbooks y documentos descargables. | [AHU Academic Catalog](img/manuales-recursos/adventhealth-catalog.jpg) · [USAHS University Catalog](img/manuales-recursos/usahs-catalog.jpg) |
| Legales | Organizar políticas, disclosures, acreditaciones y datos institucionales sin afectar la navegación principal. | [AHU Privacy Policy](img/legales/adventhealth-privacy.jpg) · [AHU Institutional Summaries](img/legales/ahu-institutional-summaries.jpg) |
| Conversión | Analizar formularios, longitud, jerarquía, consentimiento y siguiente paso. | [West Coast Request Information](img/conversion/west-coast-request-information.jpg) · [Johns Hopkins Apply](img/conversion/johns-hopkins-apply.jpg) |
| Nosotros y campus | Construir confianza mediante historia, misión, espacios, ubicación y experiencia clínica. | [AdventHealth About](img/nosotros-campus/adventhealth-about.jpg) · [Johns Hopkins About](img/nosotros-campus/jhu-about.jpg) · [MGBU About](img/nosotros-campus/mgbu-about.jpg) |

El inventario técnico completo está en [manifest.json](img/manifest.json). Este archivo debe consultarse cuando se necesite conocer la URL exacta, la fecha o el resultado de una captura.

### Estado de cobertura

| Estado | Cantidad | Observación |
|---|---:|---|
| Capturas válidas | 35 | Páginas completas guardadas y disponibles para comparación. |
| Pendientes de USAHS | 9 | `usa.edu` devolvió una pantalla de bloqueo de Cloudflare; el catálogo alojado en `catalog.usa.edu` sí pudo capturarse. |
| Pendientes de Galen | 2 | El sitio solicitó una comprobación humana. No se intentó superar la verificación ni se guardaron esas pantallas como referentes. |

Las instituciones permanecen en el Top 10 porque siguen siendo referentes relevantes. La limitación afecta únicamente a la automatización de las capturas, no a su valor como fuente de inspiración ni a los enlaces oficiales incluidos abajo.

### Cómo actualizar las capturas

El script [capture-reference-sites.mjs](../scripts/capture-reference-sites.mjs) contiene la selección de URLs y vuelve a generar las imágenes y el manifiesto. Requiere Node.js, Playwright y Chrome o Edge instalados.

```powershell
node scripts/capture-reference-sites.mjs
```

También admite una actualización parcial mediante `--only`. Se pueden pasar términos separados por comas:

```powershell
node scripts/capture-reference-sites.mjs --only=adventhealth,johns-hopkins
```

No debe completarse manualmente un CAPTCHA ni intentarse eludir una protección del sitio. Cuando una página no sea accesible, debe permanecer como pendiente en `manifest.json` hasta que pueda revisarse normalmente.

### Uso durante el rediseño

- Revisar primero la carpeta correspondiente a la página de SABER que se va a diseñar.
- Extraer patrones de jerarquía, orden y comportamiento; no copiar textos, imágenes ni identidad gráfica.
- Comparar al menos un referente de alta conversión con uno de alta reputación académica.
- Anotar qué patrón resuelve una necesidad real del usuario antes de incorporarlo al diseño.
- Validar finalmente que la solución siga siendo adecuada para la oferta más compacta de SABER College.

## Top 10

| Posición | Institución | Tipo de referente | Afinidad con SABER | Por qué conviene estudiarla | Páginas clave |
|---:|---|---|---|---|---|
| 1 | University of St. Augustine for Health Sciences | Sector, UX y conversión | Muy alta | Está especializada en ciencias de la salud, tiene presencia en Miami y organiza con claridad terapia física, enfermería y otras áreas. Combina programas, sedes, resultados, experiencias clínicas y CTA persistentes. | [Inicio](https://www.usa.edu/) · [Rehabilitative Sciences](https://www.usa.edu/college-of-rehabilitative-sciences/) · [Admissions & Aid](https://www.usa.edu/admissions-aid/) |
| 2 | AdventHealth University | Sector y arquitectura de programas | Muy alta | Es un referente directo para educación sanitaria en Florida. Su buscador y filtros de programas, la segmentación por tipo de estudiante y la integración de admisiones hacen que el recorrido sea fácil de entender. | [Inicio](https://www.ahu.edu/) · [Programas](https://www.ahu.edu/programs) · [Admisiones](https://www.ahu.edu/admissions) |
| 3 | Johns Hopkins School of Nursing | Marca, confianza y conversión | Alta | Presenta una jerarquía editorial sólida, datos de confianza, rutas claras por programa y llamadas constantes a aplicar o solicitar información sin saturar la interfaz. | [Inicio](https://nursing.jhu.edu/) · [Programas](https://nursing.jhu.edu/programs/) · [Admisiones](https://nursing.jhu.edu/admissions/) |
| 4 | University of Miami School of Nursing and Health Studies | Contexto local y marca | Alta | Es el referente geográfico más cercano. Resulta útil para estudiar cómo una institución de Miami comunica prestigio, investigación, vida estudiantil y conexión con el ecosistema sanitario local. | [Sitio oficial](https://www.sonhs.miami.edu/) · [University of Miami](https://welcome.miami.edu/) |
| 5 | West Coast University | Conversión y programas profesionales | Muy alta | Su experiencia está diseñada para estudiantes que comparan carreras sanitarias y quieren avanzar rápidamente. Destaca los requisitos, el acompañamiento de admisiones y las acciones siguientes. | [Inicio](https://westcoastuniversity.edu/) · [Programas](https://westcoastuniversity.edu/programs) · [Requisitos](https://westcoastuniversity.edu/get-started/requirements) |
| 6 | Chamberlain University | Conversión y enfermería | Alta | Es un buen referente para páginas de programa orientadas a decisión: fechas, modalidades, pasos de admisión, preguntas frecuentes y CTA visibles. Su estructura reduce la incertidumbre del futuro estudiante. | [Inicio](https://www.chamberlain.edu/) · [Programas de enfermería](https://www.chamberlain.edu/academics/nursing-school/nursing-programs) · [Futuros estudiantes](https://www.chamberlain.edu/academics/prospective-students) |
| 7 | Duke University School of Nursing | Arquitectura y contenido | Media-alta | Organiza los recorridos por nivel académico y audiencia, complementándolos con sesiones informativas, fechas de inicio y acceso al equipo de admisiones. | [Inicio](https://nursing.duke.edu/) · [Admisiones](https://nursing.duke.edu/academic-programs/admissions) · [Aplicar](https://nursing.duke.edu/apply-now) |
| 8 | Mass General Brigham University of Health Professions | Salud, rehabilitación y experiencia clínica | Alta | Su especialización en profesiones de la salud permite estudiar cómo explicar formación clínica, resultados, facultad y programas interdisciplinarios con una identidad institucional fuerte. | [Inicio](https://www.mgbu.edu/) · [Programas](https://www.mgbu.edu/academics) · [Admisiones](https://www.mgbu.edu/admissions) |
| 9 | Galen College of Nursing | Oferta enfocada y captación | Alta | Su concentración en enfermería facilita un recorrido directo desde el interés inicial hasta la sede, el programa y la solicitud. Es útil para analizar páginas enfocadas en prospectos y campañas. | [Inicio](https://galencollege.edu/) · [Programas](https://galencollege.edu/academics) · [Admisiones](https://galencollege.edu/admissions) |
| 10 | University of Pennsylvania School of Nursing | Contenido, prestigio y admisiones | Media | Es un referente aspiracional para tono editorial, credibilidad, organización de requisitos y presentación de eventos. Conviene estudiar su claridad, no replicar su complejidad institucional. | [Inicio](https://www.nursing.upenn.edu/) · [Admisiones](https://www.nursing.upenn.edu/admissions/) · [Cómo aplicar](https://www.nursing.upenn.edu/admissions/how-to-apply/) |

## Cómo usar cada grupo de referentes

### Referentes prioritarios por afinidad

1. **University of St. Augustine:** tomar como principal referencia para la arquitectura de Physical Therapist Assistant, experiencias prácticas, instalaciones y presencia en Miami.
2. **AdventHealth University:** estudiar el catálogo y el sistema para descubrir programas, modalidades y requisitos.
3. **West Coast University:** observar el recorrido de conversión desde programa hasta solicitud de información.
4. **Chamberlain y Galen:** analizar páginas de enfermería, fechas, pasos, preguntas frecuentes y captación de prospectos.

### Referentes aspiracionales

1. **Johns Hopkins:** estudiar jerarquía visual, autoridad de marca, métricas y consistencia editorial.
2. **Duke y Penn:** tomar como referencia la organización del contenido de admisiones y la claridad de las rutas académicas.
3. **University of Miami:** observar el lenguaje visual y narrativo apropiado para una institución sanitaria de Miami.

### Referentes de experiencia clínica

1. **University of St. Augustine:** laboratorios, tecnología, aprendizaje práctico y sedes.
2. **Mass General Brigham University:** integración entre formación, práctica clínica y resultados profesionales.
3. **AdventHealth University:** relación visible entre la institución educativa y el entorno real de atención sanitaria.

## Patrones recomendados para SABER College

### 1. Navegación orientada a decisiones

- Mantener en el encabezado dos acciones principales: `Request Information` y `Apply to SABER College`.
- Dar acceso directo a `Programs`, `Admissions & Tuition`, `Financial Aid` y `Student Services`.
- Separar los enlaces administrativos para estudiantes actuales de los recorridos para prospectos.

### 2. Descubrimiento de programas

- Crear una vista única de programas mediante tarjetas comparables.
- Mostrar desde la tarjeta la credencial, modalidad, ubicación y próxima fecha relevante.
- Permitir filtrar por área, modalidad o tipo de título solo si el número de programas lo justifica.

### 3. Plantilla consistente para cada programa

- Encabezado con propuesta de valor, imagen real y CTA.
- Resumen visible con duración, créditos, modalidad, campus y resultado académico.
- Secciones para plan de estudios, admisión, experiencia clínica, acreditación, costos o ayuda financiera y preguntas frecuentes.
- Resultados y datos verificables cerca del momento de decisión, con su fuente y fecha.

### 4. Confianza institucional

- Presentar acreditaciones y licencias con contexto, no como una fila aislada de logotipos.
- Incorporar laboratorios, docentes, testimonios y experiencias clínicas con fotografía auténtica.
- Facilitar el acceso a disclosures, catálogo, políticas, seguridad y datos de resultados sin competir con el recorrido comercial principal.

### 5. Conversión sin fricción

- Usar un formulario corto para el primer contacto y solicitar información adicional en pasos posteriores.
- Mantener el mismo CTA y destino en todas las páginas para evitar recorridos inconsistentes.
- Confirmar claramente el envío y ofrecer un siguiente paso en la página `Thank You`.
- Evitar pop-ups simultáneos, múltiples formularios diferentes y botones con destinos ambiguos.

### 6. Dirección visual

- Usar una composición limpia, con bloques amplios y una jerarquía tipográfica más marcada.
- Reservar el amarillo de SABER para énfasis y acciones; sostener la lectura con azul, blanco y tonos neutros.
- Favorecer fotografías propias de estudiantes, laboratorios, docentes y campus sobre imágenes genéricas.
- Emplear iconos solo cuando ayuden a comparar información, no como decoración repetitiva.

## Páginas que conviene comparar primero

| Página de SABER | Referentes principales | Qué evaluar |
|---|---|---|
| Home | USAHS, AdventHealth, Johns Hopkins | Propuesta de valor, acceso a programas, confianza y CTA iniciales. |
| Programs | AdventHealth, USAHS, West Coast | Tarjetas, filtros, información para comparar y rutas hacia cada programa. |
| Professional Nursing | Chamberlain, Galen, Johns Hopkins | Datos esenciales, admisión, plan académico, práctica clínica, resultados y FAQ. |
| Physical Therapist Assistant | USAHS, Mass General Brigham | Explicación de la profesión, aprendizaje práctico, laboratorios y carrera. |
| Admissions & Tuition | AdventHealth, Duke, Penn | Pasos, requisitos, fechas, costos, ayuda y contacto humano. |
| Request Information | West Coast, Chamberlain, Galen | Longitud del formulario, claridad del consentimiento y siguiente paso. |
| About SABER | University of Miami, Johns Hopkins | Historia, propósito, prueba de confianza e identidad local. |

## Principios para no copiar incorrectamente

- No trasladar menús extensos propios de universidades con cientos de programas.
- No usar métricas, rankings, testimonios o afirmaciones que SABER no pueda verificar.
- No imitar literalmente colores, componentes, textos ni recursos gráficos de otra institución.
- No añadir filtros o capas de navegación si una lista clara resuelve mejor la oferta actual.
- No ocultar información importante detrás de animaciones, carruseles o formularios.

## Recomendación de prioridad

Para una primera ronda de diseño, estudiar en este orden: **University of St. Augustine**, **AdventHealth University**, **West Coast University**, **Johns Hopkins School of Nursing** y **University of Miami School of Nursing and Health Studies**. En conjunto ofrecen el mejor equilibrio entre afinidad sectorial, contexto de Florida, claridad de conversión y calidad de marca.

La meta para SABER no debería ser parecer una universidad de gran tamaño, sino combinar la confianza y claridad visual de esos referentes con una experiencia más directa, cercana y fácil de completar para el futuro estudiante.
