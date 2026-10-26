# SABI Chatbot Plugin

**Proyecto:** plugin propio de chatbot para SABER College  
**Estado:** pasos 1 al 8 implementados; validación en vivo pendiente de saldo de OpenAI  
**Fecha de inicio:** 26 de octubre de 2026  
**Nombre de trabajo:** `SABI Chatbot`

## Objetivo

Construir un plugin de WordPress independiente y sencillo que permita administrar a SABI, responder preguntas institucionales, orientar al visitante y captar leads de manera segura.

El nuevo plugin se desarrollará sin desactivar ni eliminar Meow Apps AI Engine. Ambos permanecerán separados hasta que SABI Chatbot haya superado las pruebas y el cliente apruebe el reemplazo.

## Principios del producto

- Mantener la identidad, personalidad, instrucciones y recursos gráficos ya definidos para SABI.
- Facilitar la administración desde WordPress sin editar código.
- Separar la conversación de la captación de datos para reducir errores.
- No almacenar claves en Git ni exponerlas al navegador.
- No solicitar información médica, financiera, migratoria o académica sensible.
- Utilizar fuentes institucionales controladas para reducir respuestas inventadas.
- Permitir probar el chatbot sin publicarlo.
- Conservar AI Engine operativo durante todo el desarrollo.
- No copiar código ni interfaces propietarias de Meow Apps.

## Plan de desarrollo

### Paso 1. Alcance técnico y respaldo

**Acciones**

- Definir el nombre técnico, prefijos, shortcode y estructura del plugin.
- Inventariar la configuración actual de SABI en AI Engine.
- Identificar avatar, mensajes, instrucciones y documentos autorizados.
- Definir qué datos podrá recopilar el chatbot.
- Crear un respaldo específico de las opciones actuales de AI Engine.

**Entregable**

- Especificación técnica corta y estructura inicial acordada.

**Criterio de aprobación**

- El alcance no interfiere con AI Engine ni con Elementor.

### Paso 2. Estructura base del plugin

**Acciones**

- Crear el plugin `sabi-chatbot` con clases y archivos organizados.
- Añadir activación, desactivación y control de versión de datos.
- Registrar capacidades administrativas y medidas básicas de seguridad.
- Crear el shortcode propio `[sabi_chatbot]`.
- Añadir un modo de prueba que no haga llamadas externas.

**Entregable**

- Plugin instalable y activable, sin chatbot público todavía.

**Criterio de aprobación**

- Se activa y desactiva sin errores y no modifica AI Engine.

### Paso 3. Panel de administración sencillo

**Acciones**

- Crear el menú `SABI Chatbot` en WordPress.
- Organizarlo en pestañas: General, Contenido, Fuentes, Leads, Apariencia, API, Seguridad y Pruebas.
- Añadir validación, sanitización, nonces y permisos de administrador.
- Incluir importación inicial de la configuración documentada de SABI.
- Añadir una vista previa básica.

**Entregable**

- Panel funcional para configurar SABI sin editar código.

**Criterio de aprobación**

- Los cambios se guardan correctamente y no se exponen valores sensibles.

### Paso 4. Interfaz pública y sistema visual

**Acciones**

- Construir el botón flotante y la ventana de conversación.
- Aplicar el design system de SABER College.
- Incorporar el avatar y mensajes actuales de SABI.
- Implementar estados de apertura, escritura, carga, error y cierre.
- Asegurar accesibilidad por teclado, foco, etiquetas y contraste.
- Preparar comportamiento responsive para escritorio y móvil.

**Entregable**

- Chatbot visual en modo de prueba, todavía sin API.

**Criterio de aprobación**

- La interfaz coincide con la identidad de SABER y funciona correctamente en desktop y mobile.

### Paso 5. Conversación simulada y navegación

**Acciones**

- Crear respuestas controladas para programas, admisiones, matrícula, servicios y contacto.
- Añadir botones rápidos para preguntas frecuentes.
- Permitir abrir páginas oficiales desde la conversación.
- Configurar respuestas seguras cuando no exista información.
- Validar inglés y español sin utilizar todavía una API externa.

**Entregable**

- Prototipo navegable que permite validar UX, textos y recorridos.

**Criterio de aprobación**

- El flujo puede demostrarse al cliente sin costos de API.

### Paso 6. Captación estructurada de leads

**Acciones**

- Definir los campos: nombre, programa de interés, correo, teléfono y consentimiento.
- Mostrar los campos dentro de la experiencia del chat.
- Validar datos antes de enviarlos.
- Guardar los registros en una tabla propia de WordPress.
- Crear listado, búsqueda, exportación CSV y eliminación desde el panel.
- Añadir notificación por correo sin incluir información innecesaria.

**Entregable**

- Flujo de lead completo independiente de la IA.

**Criterio de aprobación**

- Un registro de prueba se guarda, se consulta, se exporta y se elimina correctamente.

### Paso 7. Fuentes y conocimiento institucional

**Acciones**

- Registrar las páginas y documentos autorizados.
- Definir prioridad entre catálogo, handbooks y páginas web.
- Preparar extracción y fragmentación del contenido de los PDF.
- Añadir fecha de revisión y estado de cada fuente.
- Evitar que documentos históricos se mezclen con la oferta vigente.
- Preparar búsqueda local de fragmentos antes de conectar el modelo.

**Entregable**

- Biblioteca de conocimiento administrable y trazable.

**Criterio de aprobación**

- Cada respuesta institucional puede asociarse con una fuente vigente.

### Paso 8. Integración con la API de IA

**Acciones**

- Añadir proveedor, modelo, endpoint y clave desde una configuración segura.
- Mantener la clave únicamente en el servidor y fuera de Git.
- Construir el prompt con personalidad, reglas, contexto y fuentes relevantes.
- Implementar timeout, reintentos controlados y mensajes de error.
- Registrar consumo técnico sin almacenar conversaciones sensibles por defecto.
- Añadir límites diarios y por sesión.

**Entregable**

- Conversaciones reales con respuestas basadas en fuentes de SABER College.

**Criterio de aprobación**

- El modelo responde correctamente y no expone claves ni instrucciones internas.

### Paso 9. Seguridad, privacidad y cumplimiento

**Acciones**

- Añadir rate limiting, protección antispam y validación del origen.
- Revisar permisos, REST API, nonces y saneamiento de datos.
- Definir consentimiento, retención y eliminación de leads.
- Integrar enlaces a Privacy Policy y Terms & Conditions.
- Bloquear la solicitud de información sensible.
- Preparar mensajes de escalamiento hacia personal de SABER College.

**Entregable**

- Lista de controles implementados y política operativa del chatbot.

**Criterio de aprobación**

- Las pruebas de seguridad y privacidad no presentan hallazgos críticos.

### Paso 10. Pruebas y control de calidad

**Acciones**

- Probar preguntas normales, ambiguas, incorrectas y maliciosas.
- Validar programas activos frente a programas históricos.
- Comprobar enlaces, formularios, correos y exportaciones.
- Revisar accesibilidad, rendimiento y compatibilidad con Elementor.
- Probar desktop, tablet y móvil.
- Comparar respuestas de SABI con el catálogo vigente.

**Entregable**

- Informe de pruebas y lista de ajustes antes de publicación.

**Criterio de aprobación**

- No existen errores críticos ni respuestas institucionales peligrosas.

### Paso 11. Piloto controlado

**Acciones**

- Publicar SABI únicamente en una página de prueba o para administradores.
- Mantener AI Engine disponible como respaldo.
- Revisar registros, errores, consumo y calidad de respuestas.
- Ajustar instrucciones y preguntas frecuentes con evidencia real.

**Entregable**

- Versión candidata para producción.

**Criterio de aprobación**

- El piloto funciona de forma estable y recibe aprobación del proyecto.

### Paso 12. Publicación y retiro gradual de AI Engine

**Acciones**

- Activar el widget propio en las páginas acordadas.
- Desactivar únicamente el chatbot público de AI Engine.
- Confirmar que no se muestran dos widgets simultáneamente.
- Mantener temporalmente AI Engine instalado para facilitar reversión.
- Retirarlo solamente después de un periodo estable y una decisión explícita.

**Entregable**

- SABI Chatbot publicado con posibilidad de rollback.

**Criterio de aprobación**

- El plugin propio opera correctamente sin depender del chatbot de AI Engine.

## Orden de aprobación

Cada paso debe aprobarse antes de iniciar el siguiente. Los pasos 1 a 5 no necesitan una API key. El paso 6 permite probar la captación de leads sin IA. La clave será necesaria por primera vez en el paso 8.

## Implementación de los pasos 1 al 8

### Decisiones técnicas

| Elemento | Decisión |
| --- | --- |
| Nombre público | `SABI Chatbot` |
| Slug | `sabi-chatbot` |
| Namespace PHP | `SaberCollege\SabiChatbot` |
| Prefijo de datos | `sabi_chatbot_` |
| Opción principal | `sabi_chatbot_settings` |
| Capacidad administrativa | `manage_sabi_chatbot` |
| Shortcode | `[sabi_chatbot]` |
| Versión actual | `0.6.1` |
| Publicación inicial | Desactivada |
| Acceso de revisión | Administradores en modo preview |
| Integración con API | Implementada con OpenAI Responses API; clave desde entorno o almacenamiento cifrado |
| Convivencia | AI Engine permanece activo y sin modificaciones |

### Respaldo

Antes de activar el plugin se generó el respaldo local:

`reports/backups/sabi-ai-engine-before-own-plugin-2026-10-26.json`

La carpeta está ignorada por Git. El respaldo contiene el chatbot existente y un resumen redactado de las opciones relevantes de AI Engine; no contiene una API key.

### Archivos principales

- `wordpress/wp-content/plugins/sabi-chatbot/sabi-chatbot.php`
- `wordpress/wp-content/plugins/sabi-chatbot/includes/class-sabi-chatbot-plugin.php`
- `wordpress/wp-content/plugins/sabi-chatbot/includes/class-sabi-chatbot-activator.php`
- `wordpress/wp-content/plugins/sabi-chatbot/includes/class-sabi-chatbot-conversation.php`
- `wordpress/wp-content/plugins/sabi-chatbot/includes/class-sabi-chatbot-api.php`
- `wordpress/wp-content/plugins/sabi-chatbot/includes/class-sabi-chatbot-settings.php`
- `wordpress/wp-content/plugins/sabi-chatbot/includes/class-sabi-chatbot-admin.php`
- `wordpress/wp-content/plugins/sabi-chatbot/includes/class-sabi-chatbot-shortcode.php`
- `wordpress/wp-content/plugins/sabi-chatbot/assets/css/admin.css`
- `wordpress/wp-content/plugins/sabi-chatbot/assets/css/sabi-chatbot.css`
- `wordpress/wp-content/plugins/sabi-chatbot/assets/js/sabi-chatbot.js`
- `wordpress/wp-content/plugins/sabi-chatbot/tests/api-contract.php`

### Panel disponible

El menú `SABI Chatbot` contiene las pestañas:

- General
- Content
- Sources
- Leads
- Appearance
- API
- Security
- Tests

La pestaña `Tests` incluye diagnósticos, una vista interactiva del shortcode y controles para revisar los estados `Ready`, `Typing`, `Loading` y `Error`. Leads administra los contactos capturados y Sources permite auditar, reconstruir y consultar la biblioteca institucional local.

### Implementación del paso 4

- Botón flotante `Ask SABI` con avatar, indicador de disponibilidad y posición configurable.
- Panel de conversación alineado con el design system de SABER College: superficies claras, azul institucional, acento amarillo y geometría sutil de marca.
- Estados visuales para interfaz lista, escritura, carga y error de conexión.
- Apertura, cierre y devolución del foco mediante JavaScript sin dependencias externas.
- Navegación por teclado, cierre con `Escape`, etiquetas ARIA, anuncios accesibles y soporte para movimiento reducido.
- Comportamiento responsive para escritorio y móvil, incluyendo panel de altura completa en pantallas pequeñas.
- Aviso de privacidad y enlace a la política de privacidad dentro del panel.
- Composer visible pero deshabilitado hasta implementar la conversación simulada y la capa segura del servidor.
- Vista previa disponible solo para administradores mientras la publicación general continúa desactivada.

### Implementación del paso 5

- Motor de conversación determinista ejecutado completamente en el navegador, sin llamadas externas.
- Trece temas controlados: saludo, programas, Professional Nursing, PTA, programas históricos, admisiones, matrícula, servicios estudiantiles, VISA I-20, solicitudes, acreditación, contacto y privacidad.
- Respuestas y controles disponibles en inglés y español, con selector manual de idioma y detección automática cuando no existe una selección explícita.
- Preguntas sugeridas que cambian según el tema para construir recorridos navegables.
- Enlaces internos a páginas oficiales existentes en el WordPress local.
- Respuesta segura para preguntas sin información verificada y escalamiento a Contact o Request Information.
- Tratamiento explícito de Medical Assisting y ESOL/VESL como programas históricos, sin presentarlos como oferta activa.
- Aviso preventivo cuando el mensaje menciona credenciales o información personal sensible.
- Botón para reiniciar la conversación sin recargar la página.
- Procesamiento únicamente en memoria: los mensajes no se guardan, no se transmiten y desaparecen al recargar.

### Implementación del paso 7

- Biblioteca local con 16 fuentes vigentes: tres documentos institucionales y trece páginas oficiales de WordPress.
- Prioridad explícita: School Catalog vigente, handbook aplicable y luego página oficial actual.
- Extracción completa y trazable de 231 páginas PDF en 636 fragmentos, con checksum, número de página y fecha de revisión.
- Indexación adicional de las páginas Elementor en fragmentos de texto, sin enviar información a servicios externos.
- Buscador administrativo que muestra fuente, tipo, página, fragmento y enlace de origen.
- Botón protegido por nonce y permisos para reconstruir la biblioteca cuando cambie una página o documento.
- Estados por fuente: registrada, indexada, vacía o faltante, junto con cobertura y observaciones.
- Los documentos y programas históricos no forman parte del índice vigente.
- Los 14 temas controlados de la conversación quedaron asociados a claves de fuentes aprobadas.
- Icono personalizado de SABI aplicado al menú administrativo de WordPress.

### Implementación del paso 8

- Integración del lado servidor con `https://api.openai.com/v1/responses`; la clave nunca se entrega al navegador.
- Credencial leída desde `OPENAI_API_KEY` en `.env.local` o capturada en un campo protegido del panel de SABI.
- Las credenciales ingresadas en WordPress se cifran con la sal de autenticación de la instalación, se guardan sin autoload y tienen prioridad sobre el entorno hasta que el administrador las elimine.
- El campo siempre se presenta vacío: permite reemplazar o eliminar la credencial, pero nunca volver a mostrarla en el navegador.
- Recuperación local de hasta seis fragmentos relevantes antes de cada pregunta libre, con prioridad y trazabilidad de la biblioteca institucional.
- Prompt protegido con personalidad de SABI, reglas bilingües, contexto verificado y tratamiento del contenido recuperado como datos no confiables.
- Marcadores `[S1]`, `[S2]` y enlaces visibles para relacionar cada respuesta con sus fuentes locales.
- Historial limitado a seis mensajes y conservado exclusivamente en la memoria del navegador.
- Timeout configurable, un único reintento para fallos temporales, errores genéricos bilingües y bloqueo previo de datos personales sensibles.
- Límites por minuto, por conversación y por día antes de llamar al proveedor.
- Tabla `wp_sabi_chatbot_usage` para estado, tokens, latencia y cantidad de fuentes; no almacena preguntas, respuestas ni texto recuperado.
- Prueba contractual sin costo que simula una respuesta válida del proveedor y verifica el recorrido completo del endpoint.
- El widget público continúa desactivado y AI Engine permanece activo y sin modificaciones.
- La prueba real llegó correctamente a OpenAI, pero la cuenta respondió `credit_balance_exhausted`; se necesita recargar saldo para aprobar una respuesta real.
- AI Engine conserva una copia preexistente de su credencial en `mwai_options`. SABI Chatbot no la usa ni la modifica; cualquier exportación SQL destinada a Git debe sanear esa opción o excluir secretos antes de publicarse.

### Montaje Docker

Solamente el plugin propio se monta desde Windows dentro del contenedor. Los plugins de terceros continúan almacenados en el contenedor para conservar el rendimiento local.

### Validaciones realizadas

| Validación | Resultado |
| --- | --- |
| Sintaxis PHP | Todos los archivos pasan `php -l`. |
| Activación | `SABI Chatbot 0.6.1` activo. |
| Convivencia | AI Engine `3.8.6` continúa activo. |
| Integridad de AI Engine | La opción `mwai_chatbots` coincide con el respaldo previo. |
| Permisos | Los administradores tienen `manage_sabi_chatbot`. |
| Menú | `SABI Chatbot` registrado con ocho pestañas. |
| Persistencia | Guardado y lectura de seis secciones editables sin pérdida de datos. |
| Visitante anónimo | El shortcode devuelve contenido vacío mientras la publicación está desactivada. |
| Administrador | El shortcode genera la vista interactiva en modo preview. |
| Credenciales | No existe una API key en las opciones del plugin. |
| Instrucciones | 4.174 caracteres con rutas oficiales y prioridad de fuentes. |
| JavaScript | El archivo del widget pasa la comprobación de sintaxis de Node.js. |
| Estados visuales | `Ready`, `Typing`, `Loading` y `Error` responden desde los controles de prueba. |
| Accesibilidad | Foco, teclado, `Escape`, ARIA, región viva y movimiento reducido implementados. |
| Responsive | Reglas específicas para escritorio y pantallas de hasta `640px`. |
| Conversación controlada | Catorce temas disponibles sin depender de una API. |
| Idiomas | Selector y respuestas comprobadas en inglés y español. |
| Navegación | Preguntas sugeridas y enlaces internos válidos por tema. |
| Fallback | Las preguntas fuera del alcance reciben una respuesta segura con vías de contacto. |
| Programas históricos | Medical Assisting y ESOL/VESL no se presentan como oferta activa. |
| Privacidad | La conversación simulada se mantiene en memoria y no se almacena ni transmite. |
| Tabla de leads | `wp_sabi_chatbot_leads` creada mediante `dbDelta` y control de versión de esquema. |
| Captura conversacional | Nombre, programa, correo, teléfono y consentimiento se solicitan uno por uno en inglés o español. |
| Activación contextual | La oferta de contacto no aparece al inicio; se presenta después de señales de interés o cuando el visitante la solicita. |
| Confirmación | Los datos permanecen en memoria y solo se envían después del consentimiento y una confirmación final. |
| Recorrido en navegador | Flujo completo validado desde interés en Nursing hasta mensaje de éxito, sin errores de consola. |
| Limpieza de QA | El registro ficticio usado para la prueba fue eliminado; tabla final en cero. |
| Validación | Nonce, honeypot, límite de cinco envíos por diez minutos y validaciones del lado servidor. |
| Prueba inválida | Solicitud con datos incompletos rechazada con respuesta HTTP `400`. |
| Prueba válida | Registro guardado, consultado y respondido correctamente en español. |
| Exportación | CSV UTF-8 verificado con protección contra fórmulas de hoja de cálculo. |
| Eliminación | Registro de prueba eliminado mediante el controlador administrativo; tabla final en cero. |
| Notificaciones | Función opcional disponible y desactivada durante las pruebas. |
| Publicación | Widget público continúa apagado; solo el preview administrativo muestra el flujo. |
| Índice PDF | 3 documentos, 231 páginas y 636 fragmentos extraídos localmente. |
| Biblioteca completa | 16 fuentes vigentes, 244 páginas lógicas y 762 fragmentos buscables. |
| Trazabilidad | Los 14 temas controlados se asocian a una o más fuentes vigentes. |
| Búsqueda local | Consultas de graduación, educación clínica y ayuda financiera devuelven fuente y página. |
| Históricos | No se registran ni indexan como parte de la oferta vigente. |
| Icono administrativo | SVG de SABI validado y aplicado al menú del plugin. |
| Configuración API | Admite entorno, constante o una credencial cifrada desde el panel; nunca guarda texto plano. |
| Cifrado de credencial | Guardado, lectura, prioridad, eliminación y retorno al entorno verificados con una clave ficticia. |
| Archivos versionados | La clave no aparece en archivos rastreados por Git y `.env.local` está ignorado. |
| Contrato API | Prueba local completa aprobada con respuesta, tokens y cuatro fuentes trazables. |
| Prueba en vivo | Solicitud enviada correctamente; OpenAI devolvió `credit_balance_exhausted`. El plugin mostró un error seguro sin filtrar datos técnicos. |
| Métricas | Se registran estado, tokens, latencia, fuentes y código técnico; no se guardan conversaciones. |
| Publicación tras paso 8 | Widget público apagado; respuestas con IA disponibles solo en el preview administrativo. |

## Estado de avance

| Paso | Estado | Notas |
| --- | --- | --- |
| 1. Alcance técnico y respaldo | Completado | Alcance definido y respaldo local generado. |
| 2. Estructura base | Completado | Plugin, shortcode y modo preview creados. |
| 3. Panel de administración | Completado | Ocho pestañas disponibles y configuración persistente. |
| 4. Interfaz pública | Completado | Base visual lista para revisión administrativa, sin API y sin publicación general. |
| 5. Conversación simulada | Completado | Prototipo bilingüe con respuestas, preguntas sugeridas, enlaces y fallback seguro. |
| 6. Captación de leads | Completado | Versión `0.4.1`: flujo conversacional bilingüe, tabla propia, búsqueda, CSV, eliminación y notificación opcional. |
| 7. Fuentes y conocimiento | Completado | Versión `0.5.0`: biblioteca local administrable, 16 fuentes, 762 fragmentos, búsqueda y trazabilidad. |
| 8. Integración con API | Implementado, pendiente de validación externa | Versión `0.6.1`: credencial cifrada, endpoint, RAG local, fuentes, límites, métricas y errores listos. Falta una clave con saldo disponible para aprobar una respuesta real. |
| 9. Seguridad y privacidad | Pendiente de auditoría final | Los controles base existen, pero falta completar y documentar la revisión integral. |
| 10. Pruebas | Pendiente | No iniciado. |
| 11. Piloto | Pendiente | No iniciado. |
| 12. Publicación | Pendiente | No iniciado. |

## Trabajo pendiente

### Cierre de validación del paso 8

- [ ] Ingresar en **SABI Chatbot > API** una credencial de OpenAI con saldo disponible o recargar la cuenta actual.
- [ ] Guardar la configuración y confirmar que `Credential source` cambia a `Encrypted WordPress storage` cuando se usa el campo del panel.
- [ ] Ejecutar preguntas reales en inglés y español desde **SABI Chatbot > Tests**.
- [ ] Confirmar que las respuestas usan información recuperada de la biblioteca y muestran referencias `[S1]`, `[S2]` y sus enlaces.
- [ ] Verificar que el panel registra solicitudes, tokens, latencia y fuentes sin almacenar el texto de la conversación.
- [ ] Confirmar desde el navegador que la credencial nunca aparece en HTML, JavaScript, respuestas AJAX ni registros visibles.

**Criterio de cierre:** al menos una conversación real bilingüe responde correctamente con fuentes institucionales, sin filtrar credenciales ni instrucciones internas.

### Paso 9. Seguridad, privacidad y cumplimiento

- [ ] Auditar nonces, capacidades administrativas, validación del origen y saneamiento de todas las entradas.
- [ ] Probar los límites por minuto, sesión y día, además del comportamiento ante abuso o automatización.
- [ ] Revisar el bloqueo de Seguro Social, tarjetas, contraseñas, documentos migratorios y otros datos sensibles.
- [ ] Definir y aplicar la retención y eliminación periódica de leads.
- [ ] Confirmar los textos de consentimiento, Privacy Policy y Terms & Conditions con el cliente.
- [ ] Revisar el contenido de exportaciones SQL para impedir que credenciales de SABI o AI Engine lleguen a Git.
- [ ] Validar los mensajes de escalamiento hacia Admissions, Student Services y personal de SABER College.
- [ ] Documentar los controles, riesgos residuales y procedimiento de respuesta ante incidentes.

**Criterio de cierre:** no existen hallazgos críticos y queda documentado cómo se protegen credenciales, conversaciones y leads.

### Paso 10. Pruebas y control de calidad

- [ ] Crear una matriz de preguntas normales, ambiguas, incorrectas, fuera de alcance y maliciosas en ambos idiomas.
- [ ] Comparar respuestas de programas, admisiones, costos, graduación y servicios con el School Catalog y los handbooks vigentes.
- [ ] Verificar que Medical Assisting y ESOL/VESL siempre se tratan como oferta histórica.
- [ ] Probar intentos de prompt injection, solicitud de instrucciones internas y preguntas con datos sensibles.
- [ ] Revisar enlaces, formularios, captación de leads, correos y exportación CSV.
- [ ] Probar errores de conexión, timeout, saldo agotado, límites y respuestas vacías.
- [ ] Revisar accesibilidad, navegación por teclado, desktop, tablet y móvil.
- [ ] Medir el impacto del widget en tiempos de carga y confirmar compatibilidad con Elementor y caché.
- [ ] Registrar resultados, evidencias, defectos encontrados y correcciones realizadas.

**Criterio de cierre:** la matriz no presenta errores críticos ni respuestas institucionales peligrosas y todos los recorridos principales funcionan.

### Paso 11. Piloto controlado

- [ ] Crear una página de piloto no indexable e insertar `[sabi_chatbot]` solamente allí.
- [ ] Activar la salida pública del shortcode sin retirar AI Engine como respaldo.
- [ ] Limitar el acceso del piloto al equipo o grupo acordado con el cliente.
- [ ] Revisar consumo, errores, fuentes utilizadas, calidad de respuestas y captación de leads durante el periodo acordado.
- [ ] Ajustar instrucciones, preguntas frecuentes y fuentes únicamente con evidencia obtenida del piloto.
- [ ] Obtener aprobación explícita antes de ampliar la publicación.

**Criterio de cierre:** el piloto permanece estable, no presenta incidentes de privacidad y cuenta con aprobación para producción.

### Paso 12. Publicación y retiro gradual de AI Engine

- [ ] Crear un respaldo final de archivos, base de datos y configuración antes del lanzamiento.
- [ ] Activar SABI en las páginas acordadas y verificar que no aparezcan dos chatbots simultáneamente.
- [ ] Desactivar solamente el chatbot público de AI Engine, sin eliminar inicialmente el plugin ni su respaldo.
- [ ] Validar en producción respuestas, enlaces, leads, correo, móvil, caché y analítica.
- [ ] Mantener un procedimiento de rollback y un periodo de observación posterior al lanzamiento.
- [ ] Retirar AI Engine solo después de estabilidad comprobada y autorización explícita del proyecto.
- [ ] Rotar o eliminar credenciales antiguas y actualizar la documentación operativa final.

**Criterio de cierre:** SABI funciona de forma estable en producción, existe rollback probado y AI Engine deja de ser necesario.

## Próxima acción propuesta

Completar el **cierre de validación del paso 8** con una credencial que tenga saldo disponible. Después se iniciará formalmente el **Paso 9: seguridad, privacidad y cumplimiento**.
