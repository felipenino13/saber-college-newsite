# Revisión Lighthouse del ambiente público de pruebas

**Fecha del reporte:** 22 de octubre de 2026  
**URL medida:** `https://abc16946.sg-host.com/`  
**Perfil:** escritorio, simulación de Lighthouse 13.5.0  
**Archivo analizado:** `C:\Users\Cris\Desktop\download`

## Resultado actual

| Categoría | Puntaje |
|---|---:|
| Performance | 98 |
| Accessibility | 89 |
| Best Practices | 88 |
| SEO | 100 |

| Métrica | Resultado | Evaluación |
|---|---:|---|
| FCP | 0.7 s | Bueno |
| LCP | 0.9 s | Bueno |
| Speed Index | 1.1 s | Bueno |
| TBT | 0 ms | Excelente |
| CLS | 0.057 | Bueno |
| Respuesta inicial | 210 ms | Bueno |

El rendimiento de escritorio ya es sólido. La prioridad inmediata no es perseguir el puntaje 100 de Performance, sino sincronizar las correcciones de accesibilidad que ya existen en local y reducir el peso de imágenes sin degradar su calidad.

## Hallazgo importante sobre la medición

La ejecución contiene recursos de varias extensiones de Chrome (`chrome-extension://...`). Estas extensiones explican aproximadamente 158 KiB del JavaScript marcado como no utilizado y prácticamente todo el JavaScript señalado como no minificado. Esos recursos no pertenecen a WordPress y no deben generar cambios en el sitio.

La próxima medición debe hacerse en Chrome Guest o en un perfil limpio sin extensiones, tanto en escritorio como en móvil.

## Acciones prioritarias

### 1. Sincronizar las correcciones locales de accesibilidad con el ambiente de pruebas

El reporte público sigue mostrando cuatro problemas que ya fueron corregidos y validados en el WordPress local:

- El Loop Grid tiene `role="list"`, pero los elementos publicados no reciben `role="listitem"`.
- El logotipo del encabezado y los iconos de Facebook, Instagram y TikTok no tienen un nombre accesible.
- El separador visual `|` del teléfono todavía se renderiza como un enlace de apenas 3 × 19 px.
- El campo de teléfono todavía bloquea el pegado.

Las correcciones locales están en:

- `scripts/fix-home-lighthouse-accessibility.php`
- `wordpress/wp-content/mu-plugins/saber-performance.php`

Después de sincronizar Elementor y el MU plugin, hay que regenerar los archivos CSS de Elementor, limpiar la caché de WordPress/SiteGround y repetir Lighthouse. Esta acción debería recuperar los resultados locales de 100 en Accessibility y Best Practices, siempre que el despliegue conserve los mismos datos y plantillas.

### 2. Optimizar las imágenes que Lighthouse identificó

Lighthouse estima un ahorro total cercano a **488 KiB**. La página transfiere aproximadamente **2.57 MiB** en 100 solicitudes; 2.38 MiB pertenecen al propio sitio.

Prioridad de optimización:

| Imagen | Peso actual aproximado | Uso observado | Acción recomendada |
|---|---:|---:|---|
| `help-venezuela.jpg` | 464 KiB | 268 × 150 px | Crear WebP de 640–768 px de ancho y conservar el original |
| `SABER_Blog-_PTA_AGO26...png` | 264 KiB | 268 × 150 px | Crear miniatura WebP 640 × 360 |
| `lanzamiento-sabri_vertv2.png` | 256 KiB | 268 × 150 px | Crear miniatura WebP 640 × 360 |
| `Saber_Nursing_23JunLand_PTA-1.png` | 104 KiB | 590 × 590 px | Servir una variante responsiva de 600–768 px |
| `sabi-nurse-banner-call-to-action.png` | 122 KiB | 960 × 610 px | Servir variante WebP cercana a 1200 px |
| `sabi-nurse-1024x1024.png` | 93 KiB | 760 × 760 px | Servir variante responsiva de 800 px |
| `chat-sabi-square-notification.png` | 14 KiB | 90 × 90 px | Crear variante de 180 × 180 para pantallas de alta densidad |

Para evitar pixelación:

- conservar siempre el archivo original;
- generar variantes de hasta 2 veces el tamaño visual cuando se necesite nitidez en pantallas Retina;
- usar WebP con calidad aproximada de 82–86 y revisar visualmente cada resultado;
- no ampliar imágenes pequeñas;
- validar las variantes en 390, 768, 1366 y 1920 px antes de reemplazarlas;
- evitar aplicar `loading="lazy"` al recurso LCP del hero.

### 3. Cargar scripts únicamente cuando sean necesarios

- El chatbot de AI Engine transfiere cerca de 99 KiB y Lighthouse estima unos 30 KiB sin uso durante la carga inicial. Conviene cargarlo al quedar el navegador inactivo o al primer clic en el botón del chatbot.
- `intl-tel-input` y sus recursos deben cargarse solo en páginas que contienen formularios telefónicos. En el Home sí son necesarios.
- Trustindex y Elfsight tienen cachés controladas por sus proveedores. Conviene confirmar que no estén resolviendo la misma función y cargar los widgets que estén debajo del primer viewport de forma diferida.
- Las consultas a Ipify e ipinfo deben revisarse para confirmar que ambas sean necesarias para el formulario. Si una sola fuente cubre el dato requerido, se puede eliminar la solicitud duplicada después de probar la captación de leads.

### 4. Tratar CSS y JavaScript bloqueantes con cautela

Lighthouse detecta jQuery, jQuery Migrate y varios archivos pequeños de Elementor en la ruta crítica. Sin embargo, el LCP ya es de 0.9 s y el TBT es de 0 ms. El beneficio potencial es pequeño.

Si se usa SiteGround Optimizer:

- probar minificación y aplazamiento en el ambiente de pruebas;
- excluir scripts de formularios, Elementor, `intl-tel-input` y captación de leads cuando se rompa alguna interacción;
- no combinar todos los archivos de forma indiscriminada;
- comprobar menú, formularios, chatbot, Loop Grid y animaciones después de cada cambio.

### 5. Repetir una medición limpia y ampliar la muestra

Ejecutar Lighthouse sin sesión de WordPress y sin extensiones en:

- Home;
- Programs;
- Nursing;
- PTA;
- Admissions;
- Accreditation;
- Student Services;
- Blog;
- una landing page.

Probar escritorio y móvil. Guardar el resultado anterior y posterior con la misma configuración. El móvil debe tener prioridad porque suele mostrar cuellos de botella que no aparecen con el perfil de escritorio.

### 6. Medir datos reales después del despliegue

Lighthouse es una prueba de laboratorio. Después de publicar en el dominio definitivo, revisar PageSpeed Insights y Search Console para obtener Core Web Vitals de usuarios reales cuando haya suficiente tráfico. En particular, confirmar LCP, CLS e INP.

## Orden recomendado

1. Sincronizar las correcciones locales de accesibilidad con el ambiente público de pruebas.
2. Limpiar cachés y ejecutar Lighthouse desde un perfil limpio.
3. Optimizar las tres imágenes más pesadas y repetir la medición.
4. Aplicar variantes responsivas al resto de imágenes señaladas.
5. Diferir el chatbot y revisar las solicitudes de terceros.
6. Probar móvil y las plantillas principales.
7. Decidir si vale la pena ajustar CSS/jQuery; con LCP de 0.9 s, esta optimización es secundaria.

## Conclusión

No existe un problema grave de rendimiento en escritorio. Las acciones con mayor valor son desplegar las correcciones de accesibilidad ya terminadas, optimizar imágenes conservando calidad y repetir la prueba sin extensiones. No se recomienda modificar jQuery o la carga de Elementor antes de completar esas acciones y medir nuevamente.
