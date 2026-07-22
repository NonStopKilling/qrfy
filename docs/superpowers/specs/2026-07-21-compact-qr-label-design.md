# Diseño de etiqueta QR compacta

## Objetivo

Eliminar el espacio blanco excesivo de la etiqueta PNG descargable sin reducir la legibilidad ni la zona de seguridad necesaria para escanear el código QR.

## Diseño aprobado

- Lienzo vertical de 1800 × 2400 px a 300 ppp.
- Encabezado corporativo sin cambios.
- QR de 1680 × 1680 px, centrado con 60 px de margen exterior.
- Conservación de la zona silenciosa de cuatro módulos incluida por el generador QR.
- Información del activo a 90 px del borde, con ancho útil de 1620 px.
- Distribución vertical compacta, sin franjas blancas decorativas ni recorte de textos.

## Verificación

- La descarga debe seguir siendo PNG y conservar 300 ppp.
- Sus dimensiones deben ser exactamente 1800 × 2400 px.
- El QR debe escanear y los textos largos deben permanecer dentro del lienzo.
