# Generated assets

Esta carpeta contiene los recursos visuales finales creados para el proyecto.
Su estructura replica la URL de WordPress para que cada imagen pueda localizarse
por la pagina donde se utiliza.

## Convencion

```text
generated/
  <seccion>/
    <slug-de-pagina>/
      <nombre>-master.<extension>
      <nombre>-web.<extension>
      README.md
```

- `master`: archivo final de mayor resolucion conservado como fuente.
- `web`: version optimizada que se importa en WordPress.
- `README.md`: modelo, prompt, referencias, integracion y validaciones.
- Los borradores y variantes descartadas permanecen en `output/imagegen/`.
- Las credenciales y claves API nunca se guardan dentro de `generated/`.
- Si una imagen aprobada cambia, se crea una nueva version en vez de sobrescribir
  el archivo anterior sin documentarlo.

## Paginas documentadas

| Pagina | Carpeta | Estado |
| --- | --- | --- |
| `/general/visa-i-20-assistance/` | `general/visa-i-20-assistance/` | Integrada y validada |
