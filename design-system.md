# SABER College Design System

**Version:** 0.2 - Direccion creativa ampliada, pendiente de aprobacion  
**Fecha:** 1 de octubre de 2026  
**Estado:** prototipo externo y aplicaciones piloto controladas en Elementor

## Proposito

Este sistema convierte las decisiones visuales que el cliente ya aprobo en una base reutilizable para las demas paginas de SABER College. La version 0.2 desarrolla la personalidad de `profile.md`, amplia los componentes y permite probar recorridos de formulario, busqueda y filtros fuera de WordPress. Sus aplicaciones piloto se documentan individualmente y no modifican los elementos protegidos.

No propone una identidad nueva. Formaliza el lenguaje que ya funciona en:

- `/`
- `/saber_college_programs/professional-nursing-program/`
- `/saber_college_programs/physical-therapist-assistant-program/`
- `/saber_college_blog/`
- `/3779-2/`

La demostracion visual esta en [design-system.html](design-system.html).

La biblioteca reutilizable en WordPress esta publicada localmente en `/saber-design-system-elementor/` (ID 2066). Usa `noindex`, se edita con Elementor y contiene bloques completos para copiar a nuevas paginas.

## Elementos protegidos

Los siguientes elementos quedan fuera del alcance de esta propuesta:

- Home aprobada por el cliente.
- Header aprobado.
- Footer aprobado.
- Logo y proporcion de marca.
- Contenido de las cinco paginas de referencia.

El sistema se deriva de esos elementos; no los reemplaza.

## Direccion visual

### Idea central

**Cuidar el futuro, paso a paso.**

Territorio creativo: **Care / Confidence / Forward**. Cuidado humano, claridad para decidir y progreso acompañado. La idea se deriva del caracter cercano, multicultural, practico y orientado al estudiante que documenta `profile.md`.

La oportunidad de SABER es comunicar una institucion que conoce a sus estudiantes y ofrece dos caminos especializados en salud. La historia en Miami desde 1972 y el apoyo personal aportan una personalidad propia.

El titular del prototipo, **Curious minds. Caring hearts. A way forward.**, es exploracion editorial; no reemplaza el descriptor oficial ni constituye un nuevo eslogan aprobado.

El sitio debe sentirse limpio, humano y profesional. El blanco es la superficie dominante. Las formas suaves y geometricas remiten al logo; la fotografia aporta autenticidad; el azul oscuro crea momentos de confianza; el amarillo guia la accion.

### Principios

1. **Blanco primero:** la mayoria de la experiencia debe respirar sobre fondos blancos o casi blancos.
2. **Fondos con identidad:** usar ondas, diagonales, triangulos suaves y halos de color; evitar rellenar cada seccion con un color solido.
3. **Fotografia como contenido:** estudiantes, docentes, campus y practica clinica deben explicar la experiencia SABER.
4. **Azul con intencion:** reservar el azul profundo para heroes secundarios, acreditacion, confianza y cierres.
5. **Amarillo como señal:** usarlo en CTA, lineas, datos y acentos; no como fondo dominante repetido.
6. **Jerarquia fuerte:** titulares compactos, parrafos legibles y acciones evidentes.
7. **Componentes flexibles:** una misma familia visual debe admitir varias composiciones para evitar paginas repetitivas.

## Tokens de color

| Token | Valor | Funcion |
| --- | --- | --- |
| `--saber-ink` | `#001A39` | Texto principal, header y footer. |
| `--saber-navy` | `#082B55` | Secciones de alto contraste y botones secundarios. |
| `--saber-blue` | `#25377B` | Fondos institucionales y profundidad. |
| `--saber-link` | `#005FBD` | Enlaces y estados interactivos; variante funcional mas oscura para texto pequeño. |
| `--saber-yellow` | `#FDC800` | CTA primario y acentos. |
| `--saber-yellow-soft` | `#FFF8E4` | Fondos calidos de lectura. |
| `--saber-sky` | `#F3F9FF` | Fondos frios y testimonios. |
| `--saber-mist` | `#F3F7FA` | Secciones neutrales. |
| `--saber-paper` | `#FFFFFF` | Superficie principal. |
| `--saber-body` | `#29435F` | Texto secundario. |
| `--saber-line` | `#DCE5EC` | Bordes y divisores. |
| `--saber-muted` | `#52677B` | Metadata y ayudas sobre superficies claras. |
| `--saber-error` | `#AA2838` | Error con mensaje e indicador de campo. |
| `--saber-success` | `#176447` | Confirmacion con texto e icono. |
| `--saber-warning` | `#775800` | Advertencias sobre fondo claro. |
| `--saber-focus` | `#005FBD` | Foco visible con separacion blanca. |

### Proporcion recomendada

- 60-70% blanco y superficies muy claras.
- 15-20% fotografia.
- 10-15% azul institucional.
- Menos de 8% amarillo de alta intensidad.

## Fondos

### `surface-clean`

- Blanco puro.
- Para lectura extensa, formularios, blog y componentes densos.
- Puede incorporar una linea amarilla o una forma muy tenue.

### `surface-wave`

- Base `#FFF8E4` o `#F3F9FF`.
- En v0.2, el patron se llama visualmente `Warm welcome`: diagonales abiertas finas y una luz calida lateral.
- Se elimina la trama radial repetitiva de v0.1 para evitar ruido visual.
- Para beneficios, valores, blog headers y agrupaciones de tarjetas.

### `surface-shards`

- En v0.2, el patron se llama visualmente `Open path`: fondo blanco con trazos abiertos y diagonales muy claras.
- Inspirado en la geometria del logo y en los heroes de programas.
- Las formas no deben competir con el contenido.

### `surface-trust`

- Azul `#25377B` o `#082B55`.
- Texto blanco y fotografia equilibrada.
- Para acreditacion, introducciones institucionales y CTA importantes.

### Regla de alternancia

Evitar secciones oscuras consecutivas. Usar normalmente un momento azul profundo por recorrido de pagina. Entre momentos de alto contraste debe existir una superficie blanca, fotografica o muy clara.

## Tipografia

**Familia principal:** Nunito  
**Fallback:** `Arial, sans-serif`

| Estilo | Desktop | Mobile | Peso | Uso |
| --- | ---: | ---: | ---: | --- |
| Display | 64-72 px | 38-54 px fluidos | 800 | Hero del prototipo; no cambia la home. |
| H1 | 56 px | 40 px | 800 | Paginas internas. |
| H2 | 42 px | 32 px | 800 | Secciones. |
| H3 | 26 px | 23 px | 800 | Tarjetas y subsecciones. |
| Eyebrow | 16-19 px | 16 px | 800 | Contexto antes del titulo. |
| Lead | 20 px | 18 px | 600 | Introduccion. |
| Body | 17 px | 16 px | 400-500 | Lectura general. |
| Small | 13-14 px | 13 px | 500-700 | Metadata y ayuda. |

Reglas:

- Limitar el ancho de parrafos a 62-72 caracteres.
- Usar interlineado de 1.55-1.75 en cuerpo.
- Evitar mayusculas sostenidas en titulares largos.
- Mantener una sola etiqueta `H1` por pagina.

## Espaciado y estructura

| Token | Valor |
| --- | ---: |
| `--space-1` | 8 px |
| `--space-2` | 12 px |
| `--space-3` | 16 px |
| `--space-4` | 24 px |
| `--space-5` | 32 px |
| `--space-6` | 48 px |
| `--space-7` | 64 px |
| `--space-8` | 88 px |

- Ancho maximo principal: `1200px`.
- Padding lateral desktop: `24px`.
- Padding lateral mobile: `20px`.
- Espacio vertical de seccion desktop: `72-96px`.
- Espacio vertical de seccion mobile: `48-64px`.

## Forma, bordes y sombras

- Radio pequeño: `10px` para botones y controles.
- Radio medio: `18px` para tarjetas.
- Radio grande: `26px` para CTA y paneles destacados.
- Bordes: `1px solid #DCE5EC` cuando una superficie blanca necesita definicion.
- Sombras: suaves, amplias y poco oscuras; no usar sombras negras duras.
- Formas graficas: esquinas redondeadas, triangulos suaves y lineas finas.

## Fotografia

### Prioridades

1. Estudiantes y docentes reales.
2. Laboratorios, aulas y campus.
3. Experiencia clinica o simulada.
4. Retratos profesionales con expresion cercana.
5. Sabi para comunidad, bienvenida y vida estudiantil.

### Tratamiento

- Aspecto recomendado para cards: `4:3` o `16:10`.
- Heroes: imagen amplia con sujeto desplazado para dejar espacio al contenido.
- Mantener tonos luminosos y naturales.
- Se permiten marcos triangulares o lineas amarillas inspiradas en el logo.
- No aplicar filtros azules intensos a todas las fotografias.
- Evitar bancos de imagenes sin relacion creible con SABER.

## Componentes prioritarios

### Boton primario

- Fondo amarillo.
- Texto azul noche.
- Radio de 10 px.
- Peso 800.
- Para la accion principal de cada seccion.

### Boton secundario

- Fondo azul profundo.
- Texto blanco.
- Para una accion complementaria.

### Boton outline

- Fondo transparente o blanco.
- Borde azul profundo.
- Para acciones de menor prioridad.

Regla: una seccion no debe competir con mas de una accion primaria.

### Tarjeta de programa

- Fotografia superior.
- Credential como eyebrow.
- Nombre del programa como H3.
- Uno o dos datos comparables.
- CTA visible.
- Superficie blanca sobre fondo con identidad.

### Tarjeta de valor

- Icono lineal.
- Titulo breve.
- Texto de dos a cuatro lineas.
- Sin boton salvo que exista una accion real.

### Tarjeta editorial

- Imagen `16:10`.
- Categoria o fecha discreta.
- Titulo de dos o tres lineas.
- Toda la tarjeta puede ser interactiva.
- Sin sombra pesada.

### Tarjeta de documento

- Icono PDF o documento.
- Nombre completo del recurso.
- Fecha o tipo cuando exista.
- Accion `View` o `Download` explicita.

### Formulario de captacion

- Superficie blanca.
- Campos de 48-52 px de alto.
- Labels visibles; placeholders no sustituyen labels.
- Consentimiento cerca del envio.
- Boton primario de ancho completo en mobile.
- Mensajes de error claros y accesibles.

### Acordeon

- Fondo blanco o transparente.
- Divisores finos.
- Icono `+` / `-` simple.
- La primera opcion solo se abre cuando ayuda a comprender el patron.

### CTA de cierre

- Fondo claro con halo amarillo o azul, o bloque azul con fotografia.
- Titulo, una frase y maximo dos botones.
- No repetir exactamente el hero.

## Patrones de hero

### Hero de conversion

- Dos columnas.
- Contenido o formulario a la izquierda.
- Fotografia real o composicion de persona a la derecha.
- Fondo claro fotografico.
- CTA amarillo.

### Hero editorial

- Fondo `surface-wave`.
- Titulo, fecha y categoria.
- Imagen destacada debajo o parcialmente superpuesta.
- Sin formulario.

### Hero institucional

- Fondo blanco o azul profundo.
- Mensaje breve y una imagen de campus, equipo o comunidad.
- Puede incluir una cifra o prueba de confianza.

## Patrones de pagina

### Programa

1. Breadcrumb.
2. Hero de conversion.
3. Resumen y datos esenciales.
4. Beneficios o experiencia de aprendizaje.
5. Curriculum.
6. Requisitos de admision.
7. Acreditacion y resultados verificables.
8. FAQ.
9. CTA final.

### Pagina institucional

1. Breadcrumb.
2. Hero institucional.
3. Introduccion sobre blanco.
4. Contenido dividido en bloques claros y fotografia.
5. Documentos o recursos.
6. CTA contextual.

### Blog

1. Hero `surface-wave`.
2. Post destacado.
3. Grid editorial de tres o cuatro columnas.
4. Categorias utiles, no excesivas.
5. Paginacion o carga progresiva accesible.

### Articulo

1. Breadcrumb.
2. Titulo, categoria y fecha sobre fondo claro con identidad.
3. Imagen destacada.
4. Cuerpo de lectura maximo `760px`.
5. Sidebar solo si aporta navegacion real.
6. Enlaces internos y CTA relacionado.

## Responsive

- Breakpoint tablet de referencia: `1024px`.
- Breakpoint mobile de referencia: `768px`.
- Todas las columnas se apilan en mobile.
- Titulares deben usar `clamp()` o valores globales equivalentes.
- Botones se vuelven de ancho completo cuando hay poco espacio.
- Imagenes conservan el foco del sujeto.
- Breadcrumbs permanecen centrados y debajo del logo.
- No debe existir scroll horizontal.

## Accesibilidad

- Contraste minimo WCAG AA.
- Foco visible en enlaces, botones y controles.
- Labels reales en formularios.
- Orden logico de encabezados.
- Texto alternativo descriptivo en imagenes informativas.
- Iconos decorativos ocultos para lectores de pantalla.
- Areas tactiles de al menos 44 x 44 px.
- Animaciones opcionales y respetando `prefers-reduced-motion`.

## Hacer / Evitar

### Hacer

- Combinar blanco, fotografia y fondos con formas sutiles.
- Crear momentos de contraste azul, no paginas enteras oscuras.
- Usar amarillo para dirigir la atencion.
- Mantener composiciones amplias y respirables.
- Variar el layout sin cambiar los tokens.
- Reutilizar componentes con contenido real.

### Evitar

- Repetir tarjetas solidas azules y amarillas en todas las secciones.
- Usar formas del logo con opacidad o tamaño que dificulten la lectura.
- Agregar botones sin destino.
- Saturar el sitio con sombras, bordes o iconos decorativos.
- Inventar metricas, testimonios o afirmaciones regulatorias.
- Modificar home, header o footer sin una solicitud y aprobacion separadas.

## Traduccion futura a Elementor

Despues de aprobar este MVP:

1. Convertir tokens en Global Colors y Global Fonts.
2. Crear clases globales para superficies, containers y componentes.
3. Construir templates de hero, cards, CTA, formularios y documentos.
4. Crear una pagina privada de componentes dentro de WordPress.
5. Aplicar el sistema a una pagina piloto.
6. Validar con el cliente antes de extenderlo.

No debe implementarse todo de una vez. Cada componente debe aprobarse y aplicarse progresivamente.

## Criterios de aprobacion

- Se reconoce visualmente como SABER College.
- Conserva la limpieza y luminosidad de la home.
- Los fondos remiten al logo sin dominar.
- Azul y amarillo tienen funciones claras.
- Los componentes se entienden en desktop y mobile.
- La propuesta puede traducirse a Elementor sin depender de codigo complejo.

## Ampliacion 0.2: personalidad en decisiones concretas

| Rasgo del perfil | Traduccion visual | Traduccion UX |
| --- | --- | --- |
| Cuidador, cercano | Nunito, retratos luminosos, bordes amables | Ayuda junto al campo, contacto humano visible, mensajes comprensibles. |
| Guia, practico | Jerarquia fuerte, lineas que orientan, secuencias numeradas | Comparador, requisitos antes de convertir, pasos cortos y recuperables. |
| Optimista, progreso | Acento amarillo, trazos abiertos ascendentes | Una accion principal por momento; siguiente paso explicito. |
| Profesional, responsable | Azul profundo, blanco, lectura serena | Documentos sin barreras, metadata real y afirmaciones con fuente. |
| Multicultural, Miami | Imagenes diversas y contexto local | Lenguaje claro; apoyo bilingue donde exista, sin simular traducciones. |
| Comunidad | Sabi, composiciones calidas, historias | Orientacion, pertenencia y vida estudiantil. |

### Firma grafica: el trazo abierto

Tres trazos finos con direccion diagonal evocan el contorno dinamico del logo. La apertura expresa avance y acompañamiento. No son una nueva version del logotipo ni deben reemplazarlo.

- Ubicar los detalles en un borde o alrededor de la imagen.
- Reservar el centro y la zona de lectura para el contenido.
- No enmarcar ni recortar caras con diagonales.
- Usar un gesto principal por composicion, no superponer varias familias de efectos.
- Radios mayores en marcos fotograficos; radios discretos en controles y documentos.
- Evitar la misma tarjeta repetida para personas, regulaciones, procesos y noticias.

### Fondos entregados

| Tratamiento | Archivo / implementacion | Contexto |
| --- | --- | --- |
| Clean paper | Blanco y CSS | Lectura, formularios, tablas. |
| Open path | `design-system-assets/path-light.svg` | Cabeceras, programas y pasos. |
| Warm welcome | `design-system-assets/path-warm.svg` | Admisiones, apoyo, comunidad y cierre. |
| Trust field | `design-system-assets/path-navy.svg` | Confianza y cierre institucional. |
| Human focus | Fotografia de biblioteca + velo CSS | Personas y contexto de aprendizaje. |
| Quiet frame | Trazo CSS sobre blanco | Recursos, texto extenso y pagina institucional. |

Los SVG son originales, ligeros y escalables. No se necesito consumir una API de imagenes ni leer credenciales. Los tres fondos carecen de texto incrustado: titulares, botones y parrafos siguen siendo editables. Si WordPress no admite SVG por su configuracion, exportar una copia WebP para biblioteca; no habilitar subidas arbitrarias solo por estos fondos.

## Catalogo ampliado y equivalencia en Elementor

| Componente | Ejemplo en HTML | Traduccion prevista |
| --- | --- | --- |
| Hero con marco fotografico | `#top` | Containers, Heading, Text, Image y Buttons. |
| Principios de marca | `#brand` | Containers, headings y divisores. |
| Paleta y jerarquia | `#foundations`, `#type` | Global Colors y Global Fonts. |
| Seis superficies | `#backgrounds` | Background Image, overlay y clases de seccion. |
| Cuatro prioridades de accion | `#components` | Button con variantes globales. |
| Boton deshabilitado | `#components` | Estado del control cuando sea necesario; no enlaces simulados. |
| Tarjetas de programa | `#programs` | Containers / Loop Item con imagen y enlace. |
| Resumen de apoyo | `#programs` | Icon List o containers. |
| Comparador accesible | `#compare` | Tabla semantica de datos; Ninja Tables existente o widget compatible. |
| Proceso de admisiones | `#journey` | Cuatro containers con numero y enlace; columna en movil. |
| Tarjetas de valor | Seccion Student First | Icon Box o containers. |
| Cabecera institucional | `#resources` | Breadcrumb existente + Heading centrado. |
| Buscador de recursos | `#resources` | Fuente de datos y filtro a implementar; no basta con un campo decorativo. |
| Filtros por categoria | `#resources` | Plugin/widget compatible o mejora personalizada accesible. |
| Fila documental | `#resources` | Loop / containers con metadata real y enlace al recurso. |
| Estado sin resultados | `#resources` | Mensaje y boton de limpiar filtros conectados al buscador. |
| Vista previa de recurso | `#resources` | En el sitio, abrir el documento real o la pagina de contexto. |
| Tarjetas de ayuda | `#support` | Icon Box, Text y Buttons. |
| Contacto directo | `#support` | Text con telefono y correo. |
| Familia de seis iconos | `#support` | Icon Library / SVG controlado. |
| Grid editorial | `#editorial` | Loop Grid / Posts con imagen, titulo, categoria y fecha reales. |
| Historia destacada | `#community` | Imagen + container editorial asimetrico. |
| Bloque de comunidad Sabi | `#community` | Image, Heading, Text y enlace. |
| Selector de ejemplos | `#forms` | Tabs accesibles; sirve al catalogo, no necesariamente a la pagina final. |
| Captacion breve | `#panel-info` | Form nativo disponible en Pro Elements / Elementor Pro. |
| Solicitud de visita por pasos | `#panel-visit` | Form con Step + Date y confirmacion por el equipo. |
| Orientacion financiera | `#panel-aid` | Form con Radio y contacto breve. |
| Error, ayuda y deshabilitado | `#states` | Estilos de campo y validacion conectada al widget real. |
| Confirmacion y fallo de conexion | `#states` / formularios | Mensajes asociados a la respuesta real del envio. |
| Acordeon | Disclosure pattern | Nested Accordion; controles accesibles. |
| Composiciones de pagina | `#patterns` | Plantillas reutilizables, sin alterar la estructura de URLs. |
| CTA de cierre | Closing pattern | Containers y Buttons con destinos reales. |

Los estilos y contenidos se pueden llevar a Elementor. Busqueda, filtros, envios, antispam y estados dinamicos requieren conectar la funcionalidad real; el HTML no constituye una importacion automatica a Elementor. No incrustar la pagina completa en un widget HTML.

## Formularios: proposito, estados y contenido

### 1. Request information

- Para contacto general, detalle de programa y cierres.
- Nombre, apellido, email, telefono opcional y programa de interes.
- Incluir `I'm still exploring` para no obligar a decidir antes de recibir ayuda.
- Confirmar el siguiente paso y el canal de contacto sin inventar plazos de respuesta.

### 2. Campus visit

- Paso 1: programa y fecha preferida.
- Paso 2: nombre, email y preguntas opcionales.
- Mantener los valores al volver al paso anterior.
- Impedir fechas pasadas en la demostracion.
- Aclarar que fecha preferida no equivale a reserva confirmada.
- El calendario del prototipo no consulta disponibilidad real.

### 3. Financial guidance

- Seleccion entre tuition, financial aid y payment options.
- Nombre y email para una primera orientacion.
- Sin informacion bancaria, ingresos ni documentos.
- Expresar `available to those who qualify` cuando se menciona ayuda financiera.
- No confundir consulta inicial, evaluacion de elegibilidad y concesion de ayuda.

### Comportamiento compartido

1. Labels persistentes y autocompletado apropiado.
2. Ayuda enlazada al control mediante `aria-describedby`.
3. Errores junto al campo y resumen anunciado; foco en el primer error.
4. Tabulacion logica y pestañas operables con flechas, Home y End.
5. Confirmacion con foco y posibilidad de reiniciar la demostracion.
6. Nada se envia ni persiste: no hay fetch, cookies ni localStorage de formulario.
7. En produccion, mostrar exito solo tras respuesta satisfactoria y conservar valores ante fallo de envio.
8. Incorporar el texto institucional aprobado de privacidad y contacto, sin preseleccionar consentimientos de marketing.

El prototipo muestra mensajes honestos de demostracion y usa botones `Preview`. No presenta una consulta como enviada al equipo. Los estados de exito y error de conexion de `#states` son muestras estaticas identificadas como tales.

## Fotografia y copy en esta propuesta

Se reutilizan imagenes existentes en la biblioteca del proyecto. Eso no prueba que representen estudiantes, egresados o instalaciones reales de SABER. Las composiciones de enfermeria se tratan como material visual de marca; la escena de PTA como imagen de aprendizaje. No se inventan identidades, testimonios, tasas de empleo ni reseñas.

Cuando se disponga de fotografia documental autorizada, priorizar:

- Una persona explicando o acompañando a otra.
- Practica visible: manos, equipo, atencion y aprendizaje.
- Encuadres a la altura de las personas, luz natural y diversidad creible.
- Espacio negativo que permita colocar texto sin cubrir rostros.

Sabi aparece en comunidad y bienvenida. Mantenerlo separado de documentacion legal, seguridad, ayuda financiera y acreditacion.

El copy nuevo solo vive en el prototipo. Conservar los textos existentes al aplicar un rediseño, salvo autorizacion expresa para editarlos. Datos como creditos y horas proceden de `profile.md` y necesitan control editorial contra el catalogo vigente antes de publicar.

## Accesibilidad y movimiento de la version 0.2

- Foco azul con separacion blanca, perceptible en fondos claros y oscuros.
- Mensajes e iconos acompañan al color; no depender de rojo/verde solos.
- El enlace funcional se oscurece a `#005FBD`.
- Botones y controles principales de al menos 44 px de alto.
- Tabla comparativa con encabezados semanticos y region desplazable por teclado.
- Una sola H1; paneles ocultos fuera de la tabulacion normal.
- Animacion breve de entrada del hero y hover sutil; se desactivan con `prefers-reduced-motion`.
- Sin autoplay, carruseles obligatorios, paralaje ni movimientos continuos.

La verificacion del prototipo no equivale a una certificacion completa de accesibilidad del sitio ni a la revision movil de WordPress.

## Estructura del entregable

```text
design-system.md
design-system.html
design-system-assets/
  system.css
  system.js
  path-light.svg
  path-warm.svg
  path-navy.svg
```

El HTML usa imagenes relativas de `wordpress/wp-content/uploads/`. Mantener esa estructura al visualizarlo; compartir solamente el HTML no incluye las imagenes ni los archivos auxiliares. Nunito se carga desde Google Fonts y requiere conexion; hay fallback si no esta disponible.

## Revision y siguiente aplicacion

Evaluar el sistema en este orden: personalidad, fondos y fotografia, componentes, formularios, composiciones. Aprobar por separado la direccion visual y cualquier copy nuevo.

Despues de la aprobacion, elegir una pagina interna piloto, traducir sus componentes a Elementor y validar su edicion. Mantener home, header, footer, textos y URLs aprobadas conforme a las instrucciones del proyecto.

### Registro de cambios

- 0.1: fundamentos visuales derivados de las paginas aprobadas.
- 0.2: territorio Care / Confidence / Forward; hero editorial; firma geometrica; tres SVG; seis fondos; comparador; admisiones por pasos; recursos con busqueda; ayuda; comunidad; tres formularios funcionales de demostracion; estados; patrones de pagina y mapeo Elementor.
- 0.2 Elementor: biblioteca publicada y `noindex` con fundamentos, superficies, tarjetas, botones, recursos, formulario sin acciones, patrones de pagina y flujo de uso. Detalle en `reports/elementor-design-system-20261002.md`.

### Verificacion de la version 0.2

- Revision visual en escritorio, tablet de 820 px y movil de 390 px; comprobacion adicional de ancho de pagina a 320 px.
- Sin desbordamiento horizontal del documento en esos tamaños.
- Las nueve imagenes del catalogo cargan; los tres SVG son XML valido.
- Una H1, IDs unicos y anclas internas con destino existente.
- Formularios: campos vacios, email invalido, confirmacion y reinicio probados.
- Visita: rechazo de fechas pasadas, avance, retorno con datos conservados y confirmacion probados.
- Pestañas: cambio con teclado y atributos de seleccion verificados.
- Recursos: busqueda, filtros, estado vacio, limpieza y vista previa probados.
- Acordeon: apertura y estado `aria-expanded` verificados.
- JavaScript verificado con `node --check`; sin errores propios de consola durante las pruebas del prototipo.
- Durante la creacion del prototipo 0.2 no se realizaron cambios en WordPress, Elementor, base de datos, home, header o footer.

### Aplicacion piloto: Student Services, 2026-10-02

- Aplicacion autorizada exclusivamente a `/student-services/` (WordPress ID 34); no implica aprobacion global ni despliegue al resto del sitio.
- Hero con fotografia existente del campus, fondo Path Light y miga de pan centrada. Tres tarjetas principales, dos accesos secundarios y seis recursos sobre Path Warm.
- Contenedores Flexbox y widgets nativos de Elementor: titulo, imagen, iconos y editores de texto. Fondos SVG registrados como medios; no hay bloques HTML cerrados ni JavaScript nuevo.
- CSS de pagina limitado a estados de foco, enlaces y pequenos ajustes de widgets; estructura, colores, tipografia y responsive configurados en Elementor.
- Textos y destinos de los once enlaces conservados exactamente. Rank Math, home, header y footer sin cambios, comprobados contra el respaldo original.
- Revision visual desktop y movil sin desbordamiento horizontal. Respaldo privado en `database/elementor-backups/`, excluido de Git.
- Detalle de implementacion y verificaciones: `reports/student-services-redesign-20261002.md`.

### Aplicacion piloto: Nursing Admissions, 2026-10-02

- Aplicacion autorizada exclusivamente a `/saber_college_programs/professional-nursing-program/nursing-admissions/` (WordPress ID 877).
- Hero con Path Light e imagen institucional de enfermeria; introduccion, ocho requisitos numerados, tres tarjetas de acompanamiento y cierre clinico de alto contraste.
- Contenedores Flexbox y widgets nativos de Elementor. Se reutilizaron los 13 campos originales de contenido, incluido el listado clinico; no se encapsulo la pagina en HTML.
- Textos, enlaces, Rank Math, home, header y footer conservados y comprobados contra el respaldo original.
- Revision visual desktop y movil sin desbordamiento horizontal. Detalle: `reports/nursing-admissions-redesign-20261002.md`.

### Aplicacion piloto: PTA Admissions, 2026-10-02

- Aplicacion autorizada exclusivamente a `/saber_college_programs/physical-therapist-assistant-program/admissions-pta/` (WordPress ID 881).
- Hero con Path Light y recurso visual propio de PTA; bloque de orientacion previa, diez criterios numerados sobre Path Warm y cierre institucional sobre Path Navy.
- Contenedores Flexbox y 21 widgets nativos de Elementor: shortcode, divisor, titulos, imagenes, editores de texto e iconos. Los 13 campos originales permanecen editables y no se encapsulo la pagina en HTML.
- Textos, enlaces, Rank Math, home, header y footer conservados y comprobados contra el respaldo original.
- Revision visual desktop y movil sin desbordamiento horizontal. Detalle: `reports/pta-admissions-redesign-20261002.md`.

### Aplicacion piloto: Apply To SABER College, 2026-10-02

- Aplicacion autorizada exclusivamente a `/apply-to-saber-college/` (WordPress ID 883).
- Hero con Path Light y fotografia del campus; introduccion editorial, cinco etapas diferenciadas y cierre de asistencia/conversion con contraste azul y amarillo.
- Contenedores Flexbox y 44 widgets nativos de Elementor: shortcode, divisor, titulos, textos, imagen, iconos y listas de iconos. Los 35 campos originales permanecen editables y no se encapsulo la pagina en HTML.
- Textos, listas, Rank Math, home, header y footer conservados y comprobados contra el respaldo original.
- Revision visual desktop y movil sin desbordamiento horizontal. Detalle: `reports/apply-saber-redesign-20261002.md`.

### Aplicacion piloto: Tuition Payment Options, 2026-10-02

- Aplicacion autorizada exclusivamente a `/general/tuition-payment-options/` (WordPress ID 885).
- Hero con Path Light e imagen existente sobre financiamiento; introduccion editorial, cuatro opciones de pago numeradas, alternativas federales anidadas y cierre de orientacion sobre Path Navy y amarillo SABER.
- Contenedores Flexbox y 22 widgets nativos de Elementor: shortcode, divisor, titulos, textos, imagen e iconos. Los 12 campos originales permanecen editables y no se encapsulo la pagina en HTML.
- Textos, enlace al documento de Direct Loans, Rank Math, home, header y footer conservados y comprobados contra el respaldo original.
- Revision visual desktop y movil sin desbordamiento horizontal. Detalle: `reports/tuition-payment-options-redesign-20261002.md`.

### Aplicacion piloto: SABER College Accreditation, 2026-10-02

- Aplicacion autorizada exclusivamente a `/saber-college-accreditation/` (WordPress ID 887).
- Hero con Path Light e imagen del campus; franja de marcas CIE, COE y CAPTE; credenciales institucionales sobre Path Warm; acreditacion programatica PTA sobre Path Navy; accesibilidad y cierre institucional.
- Contenedores Flexbox y 25 widgets nativos de Elementor: shortcode, divisor, titulos, textos, imagenes e iconos. Los 11 campos originales permanecen editables y no se encapsulo la pagina en HTML.
- Textos, Rank Math, home, header y footer conservados y comprobados contra el respaldo original. Etiquetas atomicas compatibles con el validador de Elementor.
- Revision visual desktop y movil sin desbordamiento horizontal. Detalle: `reports/accreditation-redesign-20261002.md`.

### Aplicacion piloto: Academic Affairs, 2026-10-02

- Aplicacion autorizada exclusivamente a `/student-services/academic-affairs/` (WordPress ID 896).
- Hero con Path Light e imagen institucional; tres responsabilidades en tarjetas numeradas sobre Path Warm; invitacion final con fotografia del campus y contraste Path Navy/amarillo SABER.
- Contenedores Flexbox y 10 widgets nativos de Elementor: shortcode, divisor, titulo, textos, imagenes, icono y lista de iconos. Los cinco campos originales y sus tres elementos de lista permanecen editables.
- Textos, lista, Rank Math, home, header y footer conservados y comprobados contra el respaldo original. Etiquetas atomicas compatibles con el validador de Elementor.
- Revision visual desktop y movil sin desbordamiento horizontal. Detalle: `reports/academic-affairs-redesign-20261002.md`.

### Aplicacion piloto: VISA I-20 Assistance, 2026-10-02

- Aplicacion autorizada exclusivamente a `/general/visa-i-20-assistance/` (WordPress ID 898).
- Hero internacional sobre Path Light; bienvenida en dos tarjetas; asistencia sobre Path Warm; beneficios en tres tarjetas; certificacion SEVP sobre Path Navy; proceso I-20 numerado y cierre institucional con imagen del campus.
- Contenedores Flexbox y 36 widgets nativos de Elementor: shortcode, divisor, titulos, textos, imagenes, iconos y lista de iconos. Los 21 campos originales permanecen editables y no se encapsulo la pagina en HTML.
- Textos, listas, PDF de admision, enlace SEVP, Rank Math, home, header y footer conservados y comprobados contra el respaldo original. Etiquetas atomicas compatibles con el validador de Elementor.
- Revision visual desktop y movil sin desbordamiento horizontal aparente. Detalle: `reports/visa-i20-redesign-20261002.md`.

### Aplicacion piloto: Career Services, 2026-10-02

- Aplicacion autorizada exclusivamente a `/student-services/career-services/` (WordPress ID 900).
- Hero profesional sobre Path Light; orientacion y recursos visuales sobre Path Warm; invitacion final al campus con contraste Path Navy y amarillo SABER.
- Contenedores Flexbox y 18 widgets nativos de Elementor: shortcode, divisor, titulo, textos, imagenes e iconos. Los cinco campos originales permanecen editables y no se encapsulo la pagina en HTML.
- Textos, Rank Math, home, header y footer conservados y comprobados contra el respaldo original. Etiquetas atomicas compatibles con el validador de Elementor.
- Revision visual desktop y movil sin desbordamiento horizontal aparente. Detalle: `reports/career-services-redesign-20261002.md`.

### Aplicacion piloto: Student Transcript Request, 2026-10-02

- Aplicacion autorizada exclusivamente a `/student-services/student-transcript-request/` (WordPress ID 902).
- Hero documental sobre Path Light; descarga destacada del formulario; tres pasos numerados sobre Path Warm; aviso operativo sobre Path Navy e invitacion final al campus.
- Contenedores Flexbox y 12 widgets nativos de Elementor: shortcode, divisor, titulo, textos, imagen e iconos. Los seis campos originales permanecen editables y no se encapsulo la pagina en HTML.
- Textos, formulario PDF, cinco correos de destino, Rank Math, home, header y footer conservados y comprobados contra el respaldo original. Etiquetas atomicas compatibles con el validador de Elementor.
- Revision visual desktop y movil sin desbordamiento horizontal. Detalle: `reports/student-transcript-request-redesign-20261002.md`.

### Aplicacion piloto: SABER Survey, 2026-10-02

- Aplicacion autorizada exclusivamente a `/student-services/saber-survey/` (WordPress ID 904).
- Hero institucional sobre Path Light con Sabi; declaracion de proposito en amarillo SABER; formulario principal sobre Path Warm.
- Contenedores Flexbox y 12 widgets nativos de Elementor: shortcode, divisor, titulo, textos, imagen, iconos y formulario. Los cinco elementos de contenido y los 11 campos originales permanecen editables; no se encapsulo la pagina en HTML.
- Textos, enlaces, opciones PTA/RN, configuracion completa del formulario, Rank Math, home, header y footer conservados y comprobados contra el respaldo original. Etiquetas atomicas compatibles con el validador de Elementor.
- Revision visual desktop y movil a 390 px sin desbordamiento horizontal, imagenes rotas ni errores de consola. El formulario no se envio durante las pruebas. Detalle: `reports/saber-survey-redesign-20261002.md`.

### Aplicacion piloto: SABER College Leadership, 2026-10-02

- Aplicacion autorizada exclusivamente a `/general/saber_college_leadership/` (WordPress ID 859).
- Hero editorial sobre Path Light; vision institucional; tarjetas de Executive Staff; liderazgo academico sobre Path Navy; bloques de cumplimiento, comunidad y mision.
- Contenedores Flexbox y 29 widgets nativos de Elementor: shortcode, divisor, titulos, textos, imagenes, iconos y listas de iconos. Los 18 campos originales y los 13 cargos permanecen editables; no se encapsulo la pagina en HTML.
- Textos, nombres, cargos, Rank Math, home, header y footer conservados y comprobados contra el respaldo original. Etiquetas atomicas compatibles con el validador de Elementor.
- Revision visual desktop y movil a 390 px sin desbordamiento horizontal, imagenes rotas ni errores de consola. Detalle: `reports/saber-leadership-redesign-20261002.md`.

### Aplicacion piloto: Campus Location, 2026-10-02

- Aplicacion autorizada exclusivamente a `/campus-location/` (WordPress ID 861); la landing `/lp/campus-location/` no fue modificada.
- Hero institucional sobre Path Light; ubicacion y mapa; acciones de visita sobre Path Warm; contactos de admisiones sobre Path Navy.
- Contenedores Flexbox y 18 widgets nativos de Elementor: shortcode, divisor, titulos, textos, imagen, iconos, listas de iconos y el HTML original del mapa. Los 13 campos originales permanecen editables.
- Textos, direccion, telefonos, correos, iframe de Google Maps, Rank Math, home, header y footer conservados y comprobados contra el respaldo original.
- Revision visual desktop y movil a 390 px sin desbordamiento horizontal, imagenes rotas ni errores de consola. Detalle: `reports/campus-location-redesign-20261002.md`.

### Aplicacion piloto: Gallery, 2026-10-02

- Aplicacion autorizada exclusivamente a `/general/explore-saber-college-gallery/` (WordPress ID 863).
- Hero visual sobre Path Light; historia institucional; recorrido estudiantil sobre Path Navy; tarjetas de momentos sobre Path Warm; comunidad y llamada final a Instagram.
- Contenedores Flexbox y 31 widgets nativos de Elementor: shortcode, divisor, titulos, textos, cinco imagenes, iconos y lista de iconos. Los 18 campos originales y los seis temas de galeria permanecen editables.
- Textos, enlace de Instagram, Rank Math, home, header y footer conservados y comprobados contra el respaldo original. El contenido visual usa la biblioteca local y no depende de plugins o credenciales de Instagram.
- Revision visual desktop y movil a 390 px sin desbordamiento horizontal, imagenes visibles rotas ni errores de consola. La imagen decorativa de comunidad se oculta en movil para reducir la longitud. Detalle: `reports/gallery-redesign-20261002.md`.

### Aplicacion: Net Price Calculator, 2026-10-05

- Aplicacion alojada por el plugin `saber-net-price-calculator` e integrada mediante shortcode nativo de Elementor en la pagina canonica `/general/net-price-calculator/`.
- Cabecera clara con geometria SABER; progreso de seis pasos; formularios en tarjetas; controles y botones del sistema; resumen y resultado financiero con jerarquia institucional.
- La presentacion se aplica desde `assets/saber-design-system.css`. El HTML original, los textos, los datos `2018-19` y la logica de calculo permanecen sin cambios.
- Nunito se sirve localmente bajo SIL Open Font License. La ruta tecnica `/saber-net-price-calculator-app/` conserva `noindex, nofollow`.
- El modo embebido evita una cabecera duplicada, ajusta la altura del formulario y mantiene el bloque existente como acceso por ancla. Rank Math redirige `/Net-Price-Calculator.html` con `301` a la URL canonica.
- Flujo completo verificado en escritorio y movil a 390 px, sin desbordamiento horizontal ni errores de consola. Detalle: `reports/net-price-calculator-redesign-20261005.md`.
