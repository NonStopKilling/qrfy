# Diseño de etiqueta QR compacta

## Objetivo

Eliminar el espacio blanco excesivo de la etiqueta PNG descargable sin reducir la legibilidad ni la zona de seguridad necesaria para escanear el código QR.

## Diseño aprobado

- Lienzo vertical de 1800 × 2400 px a 300 ppp.
- Encabezado corporativo sin cambios.
- QR de 1740 × 1740 px, centrado con 30 px de margen exterior.
- Conservación de la zona silenciosa de cuatro módulos incluida por el generador QR.
- Información del activo a 30 px del borde, con ancho útil de 1740 px.
- Todos los textos informativos usan el mismo tamaño de 48 px y tinta oscura; solo cambia el peso para distinguir el nombre y el código.
- Cada línea de texto se centra horizontalmente dentro del ancho útil de 1740 px, incluso cuando un texto largo se divide en varias líneas.
- La URL de la ficha directa no se imprime. El QR continúa apuntando a esa ficha.
- Distribución vertical compacta, sin franjas blancas decorativas ni recorte de textos.

## Verificación

- La descarga debe seguir siendo PNG y conservar 300 ppp.
- Sus dimensiones deben ser exactamente 1800 × 2400 px.
- El QR debe escanear y los textos largos deben permanecer dentro del lienzo.
