# Retomar cambios del 2026-08-11

## Estado

Se implementaron dos ajustes en la app Laravel ubicada en `qrfy-app`.

1. Footer público con WhatsApp.
2. Rediseño de etiqueta PNG descargable.

La suite completa quedó pasando después de los cambios.

## Footer público con WhatsApp

Archivo principal: `resources/views/layouts/base.blade.php`.

Cambios:

- Se agregó un CTA visual de WhatsApp en el footer público.
- Se copió el ícono a `public/images/whatsapp-color-svgrepo-com.svg`.
- El enlace usa `https://wa.me/56956192168`.
- El mensaje prellenado es:
  `Hola, tengo dudas respecto al Equipo.`
- El texto visible del footer queda como:
  `Hola, tengo dudas respecto al Equipo.`
- Se agregó cobertura en `tests/Feature/QrFlowTest.php` para verificar que el HTML público incluya el ícono y el enlace de WhatsApp.

Validación realizada:

- `npm run build`
- `php artisan test`
- Revisión HTTP local en `http://127.0.0.1:8000/iniciar-sesion`.

Nota de despliegue:

- `public/build` está ignorado por git en `qrfy-app`, pero se regeneró localmente con Vite.
- Si se prepara paquete para cPanel, ejecutar el script de paquete desde la raíz o copiar el build actual junto con `public/images/whatsapp-color-svgrepo-com.svg`.

## Etiqueta PNG descargable

Archivo principal: `app/Services/QrLabelPngGenerator.php`.

Cambios iniciales:

- Logo corporativo movido al lado derecho del encabezado.
- Mensaje agregado al lado izquierdo del logo:
  `EMERGENCIAS, ESCANEAR QR`
  `O LLAMAR AL +56 9 5619 2168`
- QR subido y reducido a un área de 1260 px para liberar más espacio inferior.
- Texto inferior comienza más arriba, pero tiene más aire disponible para nombres largos.
- La serie ahora se imprime sin el prefijo `Serie:`.
- Se elimina la línea `Ingrese el codigo ...`.
- El fallback manual se simplifica a:
  `Si no puede escanear:`
  `APP.GFYSERVICIOS.CL/CONSULTA/QR`

Ajuste posterior solicitado sobre resultado real:

- Se fuerza la fuente bitmap integrada de GD para todas las etiquetas, sin intentar usar Arial, DejaVu u otras TTF disponibles localmente.
- Se bajó el texto del encabezado para alinearlo verticalmente con el logo.
- El QR se volvió a agrandar a `QR_AREA = 1400` porque la descarga real se veía demasiado chica.
- Los textos principales de la etiqueta subieron a `TEXT_SIZE = 72`, con line-height de 86 px.
- El encabezado conserva `HEADER_TEXT_SIZE = 60` para mantener separación con el logo.
- Todos los textos se convierten a mayúsculas.
- Los textos se normalizan a ASCII antes de dibujarse para evitar caracteres corruptos con la fuente bitmap.
- `CODIGO:` y `SI NO PUEDE ESCANEAR:` ahora salen en mayúsculas.
- El mapa de normalización cubre acentos y `ñ`, evitando resultados como `M'AQUINAS`.

Último ajuste sobre la etiqueta descargada:

- El QR sube 15% adicional, de `QR_AREA = 1400` a `QR_AREA = 1610`.
- El bloque inferior baja a `TEXT_START_Y = 1870` para no tocar la zona silenciosa del QR.
- El line-height baja a `TEXT_LINE_HEIGHT = 76` para conservar la tipografía grande sin desbordar el lienzo.
- Se elimina el salto extra de 72 px antes de `APP.GFYSERVICIOS.CL/CONSULTA/QR`; queda como una línea más del bloque inferior.
- Solo el teléfono de cabecera sube a `HEADER_PHONE_TEXT_SIZE = 72`; `O LLAMAR AL` queda en 60 px.
- La URL manual visible de la etiqueta es la ruta de consulta `https://app.gfyservicios.cl/consulta/qr`, impresa como `APP.GFYSERVICIOS.CL/CONSULTA/QR`, no la raíz del dominio.

Último ajuste del footer público:

- `Consultar QR` pasa de link simple a botón dorado visible.
- El bloque de WhatsApp queda más compacto en tablet/desktop.
- El ícono de WhatsApp baja de 72 px aprox. a 51 px en desktop.
- El texto de WhatsApp baja de 27 px aprox. a 19 px en desktop y usa peso normal.
- La columna central del footer se estrecha para que el CTA de WhatsApp pese menos visualmente.

Validación visual realizada:

- `storage/app/codex-label-preview.png`
- `storage/app/codex-label-preview-long.png`

Validación técnica realizada:

- `php -l app/Services/QrLabelPngGenerator.php`
- `php artisan test --filter=png_label_can_be_downloaded`
- `php artisan test`
- `git diff --check`

Resultado de tests:

- 15 tests pasados.
- 76 aserciones pasadas.

## Trabajo pendiente sugerido

- Revisar visualmente una etiqueta generada desde un activo real antes de subir a producción.
- Escanear físicamente una impresión de prueba para confirmar lectura del QR con el nuevo tamaño.
- Si se aprueba el diseño, regenerar el paquete de despliegue para que `_qrfy-deploy` y el zip queden sincronizados con `qrfy-app`.
