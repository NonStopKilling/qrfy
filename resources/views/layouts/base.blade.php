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

        @php
            $whatsappUrl = 'https://wa.me/56956192168?text=' . rawurlencode('Hola, tengo dudas respecto al Equipo.');
        @endphp

        <footer class="border-t border-zinc-800 bg-black px-6 py-10 text-zinc-300 sm:px-8 md:py-12">
            <div class="mx-auto grid max-w-5xl justify-items-center gap-9 text-center md:grid-cols-[0.85fr_1fr_1.05fr] md:items-center md:gap-10 md:text-left lg:gap-12">
                <div class="flex w-full justify-center">
                    <!-- <p class="title-font text-xl font-bold text-white">GF7 Ingeniería &amp; Servicios</p> -->
                    <!-- <p class="mt-2 font-semibold text-amber-400">Despachos a todo Chile</p> -->
                    <a href="https://app.gfyservicios.cl/consulta/qr" target="_blank" rel="noopener noreferrer" class="inline-flex min-w-[11.25rem] items-center justify-center rounded-full border border-amber-300 bg-amber-400 px-6 py-3.5 text-base font-bold text-black shadow-lg shadow-amber-500/20 transition hover:bg-amber-300 md:min-w-0 md:px-5 md:py-3 md:text-sm">Consultar QR</a>
                </div>
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Abrir WhatsApp para consultar dudas sobre el equipo" class="group flex max-w-[20rem] items-center justify-center gap-3 text-white transition hover:text-emerald-300 md:mx-auto md:max-w-[16rem]">
                    <img src="{{ asset('images/whatsapp-color-svgrepo-com.svg') }}" alt="" aria-hidden="true" class="h-12 w-12 shrink-0 transition duration-200 group-hover:scale-105 sm:h-14 sm:w-14 md:h-12 md:w-12 lg:h-[3.15rem] lg:w-[3.15rem]">
                    <span class="title-font max-w-[13rem] text-left text-xl font-normal leading-tight md:max-w-[11.5rem] md:text-[1.15rem] lg:text-[1.25rem]">
                        Hola, tengo dudas respecto al <strong class="font-semibold">Equipo.</strong>
                    </span>
                </a>
                <div class="max-w-[22rem] text-sm leading-relaxed md:justify-self-center">
                    <p class="font-semibold text-white">Atención postventa</p>
                    <p class="mt-2">Lunes a viernes: 09:00 a 18:00</p>
                    <p>Sábados: 09:00 a 14:00</p>
                    <div class="mt-3 space-y-1 text-zinc-400">
                        <p><span class="font-semibold text-white">Correo:</span> <a href="mailto:contacto@gfyservicios.cl" class="text-amber-400 hover:text-amber-300">contacto@gfyservicios.cl</a></p>
                        <p><span class="font-semibold text-white">Ubicación:</span> Achao 5645, Antofagasta</p>
                    </div>
                </div>
            </div>
            <p class="mx-auto mt-9 max-w-5xl border-t border-zinc-800 px-2 pt-5 text-center text-xs leading-relaxed text-zinc-500">
                <span class="block sm:inline">
                    <span class="block sm:inline">© 2026 Todos los derechos reservados para</span>
                    <a href="https://www.gfyservicios.cl" target="_blank" rel="noopener noreferrer" class="block text-zinc-300 hover:text-amber-400 sm:inline">gfyservicios.cl</a>
                </span>
                <span class="hidden sm:inline"> · </span>
                <span class="block sm:inline">
                    QRFY hecho con amor por
                    <a href="https://www.area3.cl/" target="_blank" rel="noopener noreferrer" class="text-zinc-300 hover:text-amber-400">Area3.cl</a> 💚
                </span>
            </p>
        </footer>
    </div>
</body>
</html>
