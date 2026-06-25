<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QRFY')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap');
        :root {
            --corp-blue: #b77900;
            --brand-gold: #f5ad18;
            --ok-green: #16a34a;
            --warn-amber: #d97706;
            --alert-red: #dc2626;
        }
        body {
            font-family: 'Source Sans 3', sans-serif;
            background:
                radial-gradient(circle at 15% 8%, rgba(245, 173, 24, 0.10), transparent 24rem),
                radial-gradient(circle at 85% 55%, rgba(148, 163, 184, 0.08), transparent 30rem),
                #050505;
            min-height: 100vh;
        }
        .title-font { font-family: 'Barlow', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="text-slate-900">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-zinc-800 bg-black/95 px-5 py-5 backdrop-blur sm:px-8">
            <div class="mx-auto flex max-w-6xl flex-col items-center gap-2 sm:flex-row sm:justify-between">
                <a href="{{ route('qr.consult') }}" aria-label="Ir a consulta QR" class="block">
                    <x-company-logo class="h-24 w-auto max-w-[19rem] sm:h-28 sm:max-w-[9rem]" />
                </a>
                <p class="text-center text-xs font-semibold uppercase tracking-[0.22em] text-zinc-400 sm:text-right">Sistema de gestión<br class="hidden sm:block"> de activos QR</p>
            </div>
        </header>

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="border-t border-zinc-800 bg-black px-5 py-10 text-zinc-300 sm:px-8">
            <div class="mx-auto grid max-w-6xl gap-8 md:grid-cols-[1fr_1.1fr]">
                <div>
                    <!-- <p class="title-font text-xl font-bold text-white">GF7 Ingeniería &amp; Servicios</p> -->
                    <!-- <p class="mt-2 font-semibold text-amber-400">Despachos a todo Chile</p> -->
                    <a href="https://app.gfyservicios.cl/consulta/qr" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-sm font-semibold text-white hover:text-amber-400">Consultar QR</a>
                </div>
                <div class="grid gap-6 text-sm sm:grid-cols-2">
                    <div>
                        <p class="font-semibold text-white">Atención postventa</p>
                        <p class="mt-2">Lunes a viernes: 09:00 a 18:00</p>
                        <p>Sábados: 09:00 a 14:00</p>
                    </div>
                    <div class="space-y-1">
                        <p><span class="font-semibold text-white">Móvil:</span> <a href="tel:+56956192168" class="text-amber-400 hover:text-amber-300">+56 9 5619 2168</a></p>
                        <p><span class="font-semibold text-white">Correo:</span> <a href="mailto:contacto@gfyservicios.cl" class="text-amber-400 hover:text-amber-300">contacto@gfyservicios.cl</a></p>
                        <p><span class="font-semibold text-white">Ubicación:</span> Achao 5645, Antofagasta</p>
                    </div>
                </div>
            </div>
            <p class="mx-auto mt-8 max-w-6xl border-t border-zinc-800 pt-5 text-center text-xs text-zinc-500">
                © 2026 Todos los derechos reservados para
                <a href="https://www.gfyservicios.cl" target="_blank" rel="noopener noreferrer" class="text-zinc-300 hover:text-amber-400">gfyservicios.cl</a>
                · QRFY hecho con amor por
                <a href="https://www.area3.cl/" target="_blank" rel="noopener noreferrer" class="text-zinc-300 hover:text-amber-400">Area3.cl</a> 💚
            </p>
        </footer>
    </div>
</body>
</html>
