# Checklist final de lanzamiento — SABER College

**Proyecto:** Nuevo sitio WordPress/Elementor de SABER College  
**Preparado:** 21 de octubre de 2026  
**Objetivo:** verificar el sitio completo antes de sustituir o actualizar el dominio público.

## Estado ya verificado en local

- [x] Respaldo completo de la base de datos antes de las mejoras de indexación.
- [x] Rastreo local completo del sitemap: 125 URLs y cero errores HTTP.
- [x] Cero meta descriptions vacías, canonical ausentes, H1 incorrectos o JSON-LD inválidos.
- [x] Entidad `CollegeOrUniversity` y schema de Nursing y PTA implementados.
- [x] `llms.txt` curado con 26 páginas institucionales.
- [x] Doce parejas de artículos con `lang` y `hreflang` validados.
- [x] Cero imágenes sin texto alternativo dentro del sitemap local.
- [x] Elementor conserva estructura, widgets y texto visible.
- [x] Política `index/noindex` de WordPress y Rank Math sin cambios.
- [x] QA responsive de la página de acreditación sin overflow ni errores de consola.

## 1. Bloqueadores antes de publicar

- [ ] Crear un respaldo completo y fechado del WordPress aprobado: base de datos, uploads, plugins, temas y configuración.
- [ ] Comprobar que el respaldo se puede restaurar en un entorno separado.
- [ ] Registrar versiones de WordPress, PHP, Elementor, Elementor Pro, Rank Math, tema y plugins activos.
- [ ] Definir una ventana de publicación y evitar cambios editoriales durante la migración.
- [ ] Preparar un procedimiento de reversión si la migración falla.
- [ ] Confirmar quién aprueba el lanzamiento y quién responderá ante errores urgentes.

## 2. Formularios y captación de leads

- [ ] Enviar cada formulario desde escritorio y móvil.
- [ ] Probar los formularios de Home, Nursing, PTA, Admissions, Apply, Contact y landings.
- [ ] Confirmar que todos los campos obligatorios muestran errores claros.
- [ ] Confirmar que teléfono, correo, programa de interés y consentimiento llegan con los valores correctos.
- [ ] Confirmar que cada lead llega al CRM, correo o destino configurado una sola vez.
- [ ] Verificar remitente, destinatarios, asunto y respuesta automática.
- [ ] Verificar la página o mensaje de agradecimiento después del envío.
- [ ] Probar el comportamiento cuando el servidor rechaza o demora una solicitud.
- [ ] Confirmar que no se exponen errores técnicos ni datos privados en pantalla o consola.
- [ ] Probar protección contra spam sin impedir envíos legítimos.
- [ ] Verificar enlaces de teléfono, correo y botones de aplicación.
- [ ] Guardar una evidencia de cada formulario probado y su lead recibido.

## 3. Migración y dominio definitivo

- [ ] Migrar el sitio aprobado a `https://sabercollege.edu`.
- [ ] Actualizar `home` y `siteurl` al dominio definitivo.
- [ ] Ejecutar un reemplazo seguro de URLs serializadas desde local o staging al dominio público.
- [ ] Confirmar que no quedan referencias a `localhost`, dominios temporales o rutas internas.
- [ ] Confirmar certificado SSL válido y redirección global de HTTP a HTTPS.
- [ ] Confirmar una sola versión canónica del dominio, con o sin `www` según la decisión aprobada.
- [ ] Revisar enlaces absolutos, imágenes, fuentes, CSS, JavaScript, PDFs y videos después de migrar.
- [ ] Regenerar CSS de Elementor, enlaces permanentes, cachés y sitemap.
- [ ] Confirmar que páginas administrativas, previews y resultados de búsqueda no se indexan cuando corresponda.
- [ ] Confirmar que las páginas públicas importantes usan `index, follow` en producción.
- [ ] Crear redirecciones 301 para URLs antiguas o modificadas.
- [ ] Probar que no existen cadenas o bucles de redirección.
- [ ] Mantener un mapa de URL anterior → URL nueva.

## 4. SEO e indexación para asistentes de IA

- [ ] Rastrear nuevamente todo el dominio público sin iniciar sesión en WordPress.
- [ ] Confirmar HTTP 200, canonical público, título, description y un H1 por URL indexable.
- [ ] Confirmar que sitemap, schema y `llms.txt` contienen únicamente URLs públicas definitivas.
- [ ] Confirmar que `robots.txt` referencia el sitemap público correcto.
- [ ] Validar `CollegeOrUniversity`, `EducationalOccupationalProgram`, breadcrumbs y artículos con una herramienta pública.
- [ ] Confirmar `lang`, `hreflang` y `x-default` en las parejas bilingües.
- [ ] Probar acceso público con Googlebot, Bingbot, GPTBot, OAI-SearchBot, ChatGPT-User y ClaudeBot.
- [ ] Revisar que Cloudflare, SiteGround o el firewall no bloqueen rastreadores legítimos.
- [ ] Confirmar que las fuentes CIE, COE, CAPTE y NLN CNEA continúan vigentes.
- [ ] Enviar el sitemap a Google Search Console y Bing Webmaster Tools.
- [ ] Solicitar indexación de Home, Programs, Nursing, PTA, Admissions, Tuition y Accreditation.
- [ ] Revisar cobertura, páginas excluidas y errores de rastreo después del lanzamiento.

## 5. Performance y Core Web Vitals

- [ ] Ejecutar Lighthouse sin sesión iniciada en móvil y escritorio.
- [ ] Probar Home, Programs, Nursing, PTA, Admissions, Accreditation, Student Services, Blog y una landing.
- [ ] Medir LCP, CLS e INP con PageSpeed Insights o datos de campo cuando estén disponibles.
- [ ] Confirmar que las imágenes principales no pierden calidad ni se ven pixeladas.
- [ ] Confirmar tamaños responsivos, WebP/AVIF y dimensiones explícitas de imágenes.
- [ ] Revisar lazy loading sin aplicarlo a la imagen LCP cuando perjudique su carga.
- [ ] Revisar fuentes, CSS y JavaScript que bloqueen el renderizado.
- [ ] Confirmar caché de página, navegador, compresión y CDN.
- [ ] Probar el sitio por primera vez con caché fría y después con caché caliente.
- [ ] Revisar el peso de páginas y solicitudes de terceros.
- [ ] Guardar resultados antes y después de cualquier optimización adicional.

## 6. Responsive, navegación y experiencia de usuario

- [x] Revisar anchos aproximados de 360, 390, 768, 1024, 1366 y 1920 píxeles.
- [x] Confirmar que no existe overflow horizontal.
- [x] Probar menú principal, submenús, menú móvil y cierre del menú.
- [x] Probar breadcrumbs y footer en todas las plantillas principales.
- [x] Confirmar que botones y enlaces tienen destinos correctos y nombres comprensibles.
- [x] Detectar enlaces rotos internos, externos, teléfonos, correos, documentos y redes sociales.
- [x] Confirmar que PDFs y manuales importantes abren o descargan correctamente.
- [x] Revisar estados hover, focus, active, loading, success y error.
- [x] Confirmar que popups, banners y elementos flotantes no ocultan contenido ni controles.
- [x] Probar formularios con teclado móvil y autocompletado.
- [x] Revisar página 404, búsqueda, página de agradecimiento y estados vacíos.
- [x] Probar Chrome, Edge, Safari y Firefox, cuando estén disponibles.

Evidencia del 21 de octubre de 2026: [informe responsive, navegación y UX](responsive-ux-audit-20261021.md). Se ejecutaron 120 combinaciones por navegador en Chrome y Edge, con cero desbordamientos, recortes, controles pequeños, elementos obstructivos o errores de consola. Safari no está disponible en Windows y Firefox no está instalado; la comprobación en Safari real queda recomendada después de publicar.

## 7. Accesibilidad

- [x] Ejecutar Lighthouse Accessibility, axe o una herramienta equivalente en páginas clave.
- [x] Navegar las páginas principales usando solamente el teclado.
- [ ] Confirmar orden de foco visible y lógico. Pendiente comprobación manual del foco dentro del iframe de Google Maps; los demás controles revisados pasaron.
- [x] Confirmar etiquetas, instrucciones y errores accesibles en todos los formularios.
- [x] Revisar jerarquía H1–H6 y landmarks semánticos.
- [x] Confirmar texto alternativo útil en imágenes informativas y `alt=""` en decorativas.
- [x] Revisar contraste de texto, botones, enlaces y estados de interacción.
- [x] Confirmar que iconos enlazados tienen nombre accesible.
- [ ] Revisar modales, acordeones, carruseles, tabs y menús con lector de pantalla.
- [x] Confirmar que zoom al 200 % no oculta contenido ni funciones.
- [x] Revisar animaciones y compatibilidad con `prefers-reduced-motion`.

Evidencia del 21 de octubre de 2026: [informe de accesibilidad](accessibility-audit-20261021.md). Se probaron 16 páginas en escritorio y móvil (32 ejecuciones), con cero violaciones automáticas después de los ajustes. Queda pendiente una sesión manual con NVDA o VoiceOver y la comprobación del foco dentro del mapa de Google.

## 9. Analítica y conversiones

- [ ] Confirmar que GA4 o la plataforma aprobada recibe visitas en producción.
- [ ] Confirmar que Google Tag Manager usa el contenedor correcto.
- [ ] Medir envíos exitosos de formularios sin duplicar eventos.
- [ ] Medir clics en Apply, Request Info, teléfono, correo y descargas importantes.
- [ ] Confirmar parámetros UTM y atribución de campañas.
- [ ] Evitar enviar nombres, correos, teléfonos u otros datos personales a Analytics.
- [ ] Confirmar que las etiquetas respetan el consentimiento cuando corresponda.
- [ ] Excluir tráfico interno y administrativo de los informes cuando sea necesario.
- [ ] Crear una prueba de conversión y conservar evidencia en DebugView o herramienta equivalente.
- [ ] Definir responsables y frecuencia para revisar leads, conversiones y errores.

## 10. Seguridad y operación

- [ ] Actualizar WordPress, tema y plugins en una copia antes de actualizar producción.
- [ ] Eliminar plugins, temas y usuarios que no sean necesarios.
- [ ] Confirmar roles con el mínimo acceso necesario y contraseñas seguras.
- [ ] Activar autenticación de dos factores para administradores cuando esté disponible.
- [ ] Confirmar que debug, logs públicos y mensajes detallados están desactivados en producción.
- [ ] Revisar permisos de archivos y protección del área administrativa.
- [ ] Probar copias automáticas de base de datos y archivos.
- [ ] Confirmar SMTP o sistema de entrega de correos y revisar SPF, DKIM y DMARC.
- [ ] Configurar monitoreo de disponibilidad, certificados y formularios.
- [ ] Revisar logs de errores PHP, servidor, 404 y seguridad después de publicar.

## 11. Día del lanzamiento

- [ ] Crear el respaldo final inmediatamente antes de migrar.
- [ ] Activar una ventana breve de mantenimiento si es necesaria.
- [ ] Ejecutar la migración y limpiar todas las cachés.
- [ ] Probar Home, programas, formularios, documentos y navegación sin iniciar sesión.
- [ ] Confirmar canonical, robots, sitemap y schema en el dominio definitivo.
- [ ] Confirmar que no hay errores graves en consola ni logs del servidor.
- [ ] Confirmar que los leads reales de prueba llegan a su destino.
- [ ] Retirar el modo mantenimiento únicamente después de pasar los bloqueadores.
- [ ] Guardar capturas, reportes y hora de publicación.

## 12. Seguimiento posterior

- [ ] Revisar formularios, errores y uptime durante las primeras horas.
- [ ] Revisar Search Console y Bing durante los primeros días.
- [ ] Revisar 404 y crear redirecciones adicionales cuando tengan un equivalente válido.
- [ ] Revisar Core Web Vitals cuando existan suficientes datos de campo.
- [ ] Comparar leads y conversiones con la línea base anterior.
- [ ] Corregir primero problemas de rastreo, formularios, seguridad o cumplimiento.
- [ ] Documentar cada cambio posterior con fecha, evidencia y responsable.

## Criterios para autorizar publicación

- [ ] Los formularios críticos envían y reciben correctamente.
- [ ] No existen errores HTTP, enlaces rotos críticos ni recursos bloqueados.
- [ ] Canonical, sitemap, robots, schema y `llms.txt` usan el dominio definitivo.
- [ ] El sitio es usable en móvil, escritorio y teclado.
- [ ] Los textos regulatorios y datos de programas están aprobados.
- [ ] Analytics registra conversiones sin datos personales.
- [ ] Existe respaldo restaurable y procedimiento de reversión.
- [ ] La persona responsable ha aprobado el lanzamiento.
