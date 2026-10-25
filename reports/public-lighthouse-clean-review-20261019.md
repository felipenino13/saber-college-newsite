# Lighthouse público sin sesión — revisión del home

Fecha: **19 de octubre de 2026**  
URL: **https://abc16946.sg-host.com/**  
Reporte entregado: **Lighthouse 13.4.1, escritorio, sesión de WordPress cerrada**  
Prueba complementaria: **Lighthouse 13.5.0, móvil limpio, incógnito y sin extensiones**

## Estado del servidor

El home vuelve a responder correctamente desde la caché pública:

- HTTP **200**.
- Aproximadamente **239 KB de HTML**.
- `X-Proxy-Cache: HIT`.
- La respuesta vacía de `Content-Length: 0` observada anteriormente ya no está presente.

## Resultados

| Perfil | Performance | Accessibility | Best Practices | SEO | FCP | LCP | TBT | CLS | Peso |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Escritorio, reporte entregado | 97 | 89 | 88 | 100 | 0.8 s | 0.9 s | 0 ms | 0.049 | 2.4 MB |
| Móvil limpio, prueba complementaria | 94 | 92 | 88 | 100 | 1.8 s | 2.5 s | 46 ms | 0.068 | 2.3 MB |

El rendimiento es bueno en ambos perfiles. No se recomienda arriesgar la estabilidad de Elementor o de los formularios intentando eliminar cada milisegundo restante.

## Problemas reales pendientes

### 1. Semántica del Loop Grid de noticias

El contenedor de noticias usa `role="list"`, pero sus hijos directos no tienen `role="listitem"`. Lighthouse detecta tres tarjetas.

Solución: mantener el Loop Grid editable y añadir `role="listitem"` a cada elemento directo de `.elementor-loop-container[role="list"]`. Puede implementarse con un filtro de renderizado de Elementor o una mejora pequeña aplicada al DOM; no requiere convertir el componente en HTML plano.

### 2. Cuatro enlaces sin nombre accesible

- Enlace del logotipo del header.
- Facebook.
- Instagram.
- TikTok.

Solución nativa en Elementor:

- Definir un texto alternativo útil para el logotipo, por ejemplo `SABER College home`.
- En cada icono social, añadir en **Advanced → Attributes**: `aria-label|Facebook`, `aria-label|Instagram` y `aria-label|TikTok`.

### 3. Separador telefónico demasiado pequeño en escritorio

El carácter `|` del encabezado es un enlace independiente de apenas 3 × 19 px y comparte el mismo destino telefónico que el número.

Solución: convertir el separador en texto normal y conservar únicamente el número `(305) 443-9170` como enlace. No es necesario agrandar visualmente el separador.

### 4. El campo telefónico bloquea el pegado

El widget HTML del formulario contiene un listener `paste` que ejecuta `event.preventDefault()` para números que no comienzan por `+1`. Esto produce el único fallo de Best Practices.

Solución: retirar el bloqueo de pegado y validar o normalizar el número después de pegar, durante `input`, `blur` o el envío. `intl-tel-input` ya está configurado con Estados Unidos, `strictMode: true` y `onlyCountries: ["US"]`, por lo que se puede conservar la restricción sin impedir una función básica del navegador.

## Oportunidades menores de rendimiento

- Lighthouse estima unos **160 KiB** adicionales en imágenes. Las principales oportunidades están en el póster del video, las banderas de `intl-tel-input`, imágenes del blog y el logotipo.
- Los recursos que bloquean renderizado representan aproximadamente **390 ms** simulados, principalmente jQuery y hojas pequeñas de Elementor.
- Con **94 móvil y 97 escritorio**, no se recomienda diferir jQuery ni cargar de forma asíncrona el CSS principal sin una regresión completa de formularios, menús y Elementor.
- El LCP móvil queda en **2.48 s**, justo dentro del objetivo de 2.5 s de esta prueba de laboratorio. Conviene confirmar después con datos reales de usuarios cuando el dominio reciba tráfico suficiente.

## Prioridad recomendada

1. Corregir los nombres accesibles del logotipo y redes sociales.
2. Corregir la semántica del Loop Grid.
3. Convertir el separador telefónico en texto normal.
4. Permitir pegar en el teléfono y validar después.
5. Repetir Lighthouse móvil y escritorio sin sesión, extensiones ni barra de administración.

Estas correcciones pueden mantenerse completamente editables en Elementor y no requieren cambiar el contenido visible.
