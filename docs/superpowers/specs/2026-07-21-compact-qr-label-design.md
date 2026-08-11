# Diseño de etiqueta QR compacta

## Objetivo

Eliminar el espacio blanco excesivo de la etiqueta PNG descargable sin reducir la legibilidad ni la zona de seguridad necesaria para escanear el código QR.

## Diseño aprobado

- Lienzo vertical de 1800 × 2400 px a 300 ppp.
- Encabezado negro de 250 px con franja amarilla inferior de 18 px.
- Mensaje de emergencia a la izquierda del encabezado:
  `EMERGENCIAS, ESCANEAR QR`
  `O LLAMAR AL +56 9 5619 2168`
- Logo corporativo alineado a la derecha del encabezado, con ancho máximo de 590 px.
- QR de 1610 × 1610 px, centrado horizontalmente y ubicado arriba para reducir el padding lateral observado en descarga.
- Conservación de la zona silenciosa de cuatro módulos incluida por el generador QR.
- Información del activo a 30 px del borde, con ancho útil de 1740 px.
- Todos los textos de la etiqueta usan la fuente bitmap integrada de GD, igual que el comportamiento observado en producción.
- Los textos principales usan tamaño visual de 72 px, un aumento aproximado de 20% respecto de la iteración previa.
- El texto de emergencia del encabezado mantiene 60 px para no invadir el logo derecho.
- Solo el número de teléfono del encabezado usa 72 px para reforzar su visibilidad.
- Todos los textos se imprimen en mayúsculas.
- Los textos se normalizan a ASCII antes de dibujarse para evitar caracteres rotos en la fuente bitmap.
- Cada línea de texto se centra horizontalmente dentro del ancho útil de 1740 px, incluso cuando un texto largo se divide en varias líneas.
- La etiqueta reserva más espacio inferior para nombres de activo que salten a 2 o 3 líneas.
- La serie se imprime sin el prefijo `Serie:` para ahorrar una línea visual.
- El código interno se mantiene como `CODIGO: QR-...`.
- La URL de la ficha directa no se imprime. El QR continúa apuntando a esa ficha.
- El fallback manual se imprime en dos niveles:
  `Si no puede escanear:`
  `APP.GFYSERVICIOS.CL/CONSULTA/QR`
- Distribución vertical compacta, sin franjas blancas decorativas ni recorte de textos.

## Implementación vigente

- Servicio: `app/Services/QrLabelPngGenerator.php`.
- Constantes principales:
  - `HEADER_HEIGHT = 250`
  - `DIVIDER_HEIGHT = 18`
  - `QR_AREA = 1610`
  - `QR_TOP = 270`
  - `TEXT_START_Y = 1870`
  - `HEADER_TEXT_SIZE = 60`
  - `HEADER_PHONE_TEXT_SIZE = 72`
  - `TEXT_SIZE = 72`
  - `HEADER_TEXT_LINE_HEIGHT = 68`
  - `TEXT_LINE_HEIGHT = 76`
  - `HEADER_TEXT_Y = 116`
- El QR sigue codificando la URL pública directa del activo.
- `displayConsultUrl()` muestra la URL de consulta sin protocolo y en mayúsculas, por ejemplo `APP.GFYSERVICIOS.CL/CONSULTA/QR`.
- La etiqueta no busca ni usa fuentes TTF del servidor. El dibujo de texto siempre pasa por `imagestring()` escalado.

## Verificación

- La descarga debe seguir siendo PNG y conservar 300 ppp.
- Sus dimensiones deben ser exactamente 1800 × 2400 px.
- El QR debe escanear y los textos largos deben permanecer dentro del lienzo.
- Validar con `php artisan test`.
- Muestras visuales generadas durante el ajuste:
  - `storage/app/codex-label-preview.png`
  - `storage/app/codex-label-preview-long.png`
