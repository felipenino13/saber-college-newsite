# Home: correcciones de Lighthouse en Elementor

Fecha: 2026-10-19  
Entorno modificado: WordPress local en `http://localhost:8082/`  
Sitio público: sin cambios

## Alcance

Se corrigieron los hallazgos de accesibilidad y buenas prácticas identificados en la medición pública limpia de `https://abc16946.sg-host.com/`, manteniendo los componentes editables en Elementor.

## Cambios aplicados

- **Home, página 7:** se retiró el bloqueo de pegado del campo telefónico. La regla de Estados Unidos durante la escritura y la validación final con `iti.isValidNumber()` continúan activas.
- **Header, plantilla 16:** el separador visual `|` dejó de ser un enlace telefónico. El logotipo ahora usa el texto alternativo `SABER College home`.
- **Footer, plantilla 53:** los enlaces de Facebook, Instagram y TikTok recibieron nombres accesibles desde los atributos nativos de Elementor.
- **Loop Grid de noticias:** cada elemento generado recibe `role="listitem"` en el renderizado. El Loop Grid y su plantilla siguen siendo widgets editables de Elementor.

## Integridad de Elementor

La estructura se mantuvo intacta:

| Documento | Nodos antes | Nodos después | IDs y tipos de widget |
|---|---:|---:|---|
| Home 7 | 197 | 197 | Sin cambios |
| Header 16 | 18 | 18 | Sin cambios |
| Footer 53 | 34 | 34 | Sin cambios |
| Loop Item 312 | 6 | 6 | Sin cambios |

El script se ejecutó dos veces después del ajuste final. La segunda ejecución no produjo cambios, lo que confirma que es idempotente.

## Verificación funcional y visual

- HTTP 200 en escritorio y móvil.
- Sin desbordamiento horizontal en 1440 px ni 390 px.
- Los 4 elementos del Loop Grid tienen `role="listitem"`.
- Logotipo y tres enlaces sociales tienen nombres accesibles.
- El separador telefónico ya no es un enlace.
- Se pegó `+13055551234` correctamente en el teléfono tanto en escritorio como en móvil.
- Sin errores de consola ni solicitudes fallidas durante la prueba.
- La revisión visual completa no mostró cambios visibles inesperados.

## Lighthouse local posterior

| Perfil | Performance | Accessibility | Best Practices | SEO |
|---|---:|---:|---:|---:|
| Desktop | 94 | 100 | 100 | 100 |
| Mobile | 78 | 100 | 100 | 100 |

Los cuatro audits que motivaron el ajuste pasan en ambos perfiles:

- `aria-required-children`
- `link-name`
- `target-size`
- `paste-preventing-inputs`

El puntaje de Performance local no debe compararse directamente con el servidor público porque el entorno local no incluye la caché y la entrega de SiteGround. La medición pública limpia anterior ya registraba 97 en desktop y 94 en mobile; después de desplegar estos cambios debe repetirse Lighthouse en la URL pública.

## Evidencia

- Respaldo previo: `reports/backups/lighthouse-accessibility-20261019/`
- Estado posterior y Lighthouse: `reports/home-lighthouse-accessibility-after-20261019/`
- Script de cambios Elementor: `scripts/fix-home-lighthouse-accessibility.php`
- Ajuste de renderizado: `wordpress/wp-content/mu-plugins/saber-performance.php`
