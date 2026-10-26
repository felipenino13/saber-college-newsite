# SABI Chatbot

**Fecha de configuración:** 26 de octubre de 2026  
**Plugin:** Meow Apps AI Engine `3.8.6`  
**ID interno del chatbot:** `default`  
**Estado:** configurado, no publicado globalmente y sin conexión activa a una API

## Objetivo

SABI quedó preparado como guía virtual de SABER College para orientar a visitantes sobre programas, admisiones, recursos de matrícula y servicios estudiantiles. La configuración conserva el personaje institucional de Sabi, pero establece límites claros para evitar respuestas inventadas o afirmaciones que puedan interpretarse como garantías académicas, financieras, migratorias o laborales.

## Configuración aplicada

| Área | Configuración |
| --- | --- |
| Nombre administrativo | `SABI - SABER College` |
| Nombre visible | `SABI` |
| Idioma | Responde en el idioma del visitante; inglés como idioma predeterminado. |
| Tono | Cercano, profesional, conciso, acogedor y centrado en el estudiante. |
| Identidad | Sabi representa fortaleza, adaptación, resiliencia, curiosidad, perseverancia, servicio, confianza y sentido de pertenencia. |
| Mensaje inicial | Presenta a SABI como guía virtual y ofrece ayuda sobre programas, admisiones, matrícula y servicios estudiantiles. |
| Campo de consulta | Máximo de 600 caracteres y texto orientativo específico. |
| Contexto de página | Activado para que, cuando exista conexión con el modelo, pueda considerar el contenido de la página donde aparece. |
| Memoria local | Activada para conservar la conversación en el navegador del visitante. |
| Archivos | Carga de imágenes y documentos desactivada. |
| Herramientas | Web search, funciones, MCP y generación de imágenes desactivados. |
| Respuesta | Temperatura `0.2`, máximo `900` tokens y hasta `12` mensajes de contexto. |
| Apariencia | Tema `timeless`, ventana emergente, animación `zoom` y posición inferior derecha. |
| Avatar | `/wp-content/uploads/2026/08/chat-sabi-square-notification.png` |
| Etiqueta del botón | `Ask SABI` |
| Privacidad | Incluye aviso para no compartir información personal sensible. |

## Conocimiento institucional incluido

Las instrucciones del bot contienen una síntesis controlada de SABER College:

- Institución privada sin fines de lucro, fundada en 1972 y ubicada en Miami.
- Dirección, teléfono general y correo institucional.
- Oferta activa limitada a `Professional Nursing (A.S.)` y `Physical Therapist Assistant (A.S.)`.
- Resumen básico de ambos programas según el contenido vigente del proyecto.
- Aclaración expresa de que `Medical Assisting` y `ESOL/VESL` son programas históricos y no deben presentarse como oferta activa.
- Rutas internas oficiales para programas, admisiones, solicitud, información, matrícula, servicios estudiantiles, ayuda VISA I-20, acreditación y contacto.
- Regla de responder en inglés o español según el idioma del visitante.

## Documentos preparados como fuentes

Estos documentos están identificados dentro de las instrucciones y preparados para una futura base de conocimiento:

| Prioridad | Documento | Ruta local |
| --- | --- | --- |
| 1 | School Catalog – 4-22-2026 (V2) | `/wp-content/uploads/2026/04/School-Catalog-4-22-2026-V2.pdf` |
| 2 | Professional Nursing Program Student Handbook – 7-1-2025 | `/wp-content/uploads/2026/02/PROFESSIONAL-NURSING-PROGRAM-STUDENT-HANDBOOK-7-1-2025.pdf` |
| 3 | PTA 2025-2026 Student Handbook | `/wp-content/uploads/2026/02/PTA-2025-2026-PTA-Student-Handbook.pdf` |

La prioridad definida es: catálogo escolar vigente, handbook vigente del programa correspondiente, página oficial actual y finalmente el resumen institucional del prompt. Si existen diferencias entre fuentes, SABI debe indicarlo y remitir al catálogo vigente o a SABER College, sin resolver la diferencia mediante suposiciones.

## Reglas de seguridad y precisión

SABI tiene instrucciones para no inventar ni garantizar:

- costos, ayuda financiera, becas o transferencia de créditos;
- elegibilidad de admisión, horarios, fechas de inicio o disponibilidad de cupos;
- graduación, licenciamiento, acreditación, exámenes o prácticas clínicas;
- empleo, salarios, resultados migratorios o resultados profesionales.

También debe evitar asesoría médica, legal, financiera o migratoria; no solicitar datos sensibles; identificarse siempre como guía virtual; reconocer cuando no conoce una respuesta; y dirigir al visitante a la página u oficina apropiada.

## Pendiente por falta de API key

No se configuró ninguna credencial. El entorno OpenAI existente continúa sin API key y no fue modificado.

Quedan pendientes estas acciones:

1. Agregar la API key desde el panel seguro de AI Engine, sin escribirla en Git, archivos Markdown ni código fuente.
2. Confirmar el modelo que se utilizará y sus límites de costo.
3. Crear o seleccionar el entorno de embeddings requerido por `Knowledge`.
4. Indexar el catálogo y los handbooks indicados arriba.
5. Probar respuestas en inglés y español contra preguntas de admisiones, programas y costos.
6. Revisar consumo, límites por visitante y registro de conversaciones.
7. Publicar el chatbot globalmente o insertarlo por shortcode únicamente después de las pruebas.

## Publicación

El bot no se activó como chatbot global porque todavía no puede responder sin credenciales. Esto evita mostrar en el sitio una interfaz que falle al enviar mensajes.

Cuando esté validado, AI Engine puede publicarlo globalmente con el ID `default` o insertarlo de forma controlada con:

```text
[mwai_chatbot id="default"]
```

## Reproducción de la configuración

La configuración, sin secretos, quedó automatizada en `scripts/configure-sabi-chatbot.php`. Se aplica dentro del contenedor con:

```powershell
docker cp scripts/configure-sabi-chatbot.php sabercollege_newsite_wordpress:/tmp/configure-sabi-chatbot.php
docker compose exec -T -e SABER_APPLY_CHATBOT_CONFIG=1 wordpress wp eval-file /tmp/configure-sabi-chatbot.php --allow-root
```

El script es idempotente: actualiza el chatbot `default`, conserva la configuración del proveedor y no añade, muestra ni modifica claves de API.
