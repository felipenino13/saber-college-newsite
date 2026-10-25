# Correccion del envio de leads en PTA y home

Fecha: 2026-10-16

## Causa

El manejador personalizado del formulario buscaba `#form-field-program`. Elementor renderiza ese campo de tipo radio como `#form-field-program-0` y `#form-field-program-1`, por lo que el selector devolvia `null`. La lectura posterior de `program.value` producia el error observado y detenia el envio.

## Cambio aplicado

- Plantilla compartida de Request Information: post 2015, widget HTML `518a0bf`.
- Copia del formulario en el home: post 7, widget HTML `29f746e`.
- El programa seleccionado ahora se obtiene al enviar mediante `input[name="form_fields[program]"]:checked`.
- Se agregaron comprobaciones para manejar de forma segura el caso en que no exista una opcion seleccionada.
- No se modificaron los campos, acciones ni la estructura editable de los formularios de Elementor.

## Verificacion

- Reproduccion anterior: el selector devolvia cero elementos y aparecia `Cannot read properties of null (reading 'value')`.
- PTA y home responden HTTP 200.
- Prueba con telefono invalido: cero errores y cero solicitudes de lead.
- Prueba completa con datos sinteticos validos: los POST previstos para VerityIQ y el correo fueron interceptados y respondidos localmente; no se transmitio ningun lead real.
- Cero errores de pagina y consola en ambas paginas durante la prueba completa.
- Los conteos de nodos de Elementor permanecieron en 6 para la plantilla y 157 para el home.
- Todo el JSON de Elementor fuera de los dos widgets HTML intervenidos quedo identico.
- Las configuraciones de ambos widgets de formulario quedaron identicas.
- Una segunda ejecucion devolvio `already_applied`, confirmando idempotencia.

## Alcance

El cambio esta aplicado solamente en el entorno local. La prueba confirma el flujo del navegador hasta la creacion de las dos solicitudes salientes, pero no confirma la recepcion final por los servicios externos porque ambas solicitudes se interceptaron para evitar generar un lead de prueba.

