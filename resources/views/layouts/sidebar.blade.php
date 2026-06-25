@php $role = $role ?? 'tecnico'; @endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QRFY')</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                radial-gradient(circle at 75% 10%, rgba(245, 173, 24, 0.09), transparent 28rem),
                radial-gradient(circle at 45% 80%, rgba(148, 163, 184, 0.07), transparent 32rem),
                #050505;
            min-height: 100vh;
        }
        .title-font { font-family: 'Barlow', sans-serif; }
    </style>
</head>
<body class="text-slate-900">
<div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
    <aside class="fixed inset-y-0 left-0 z-30 flex w-72 flex-col border-r border-zinc-800 bg-black p-5 text-zinc-100 shadow-2xl shadow-black/50 transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="mb-7 flex items-start justify-between">
            <x-company-logo class="h-28 w-56" />
            <button class="rounded-lg border border-zinc-700 px-3 py-2 text-xs lg:hidden" @click="sidebarOpen = false">Cerrar</button>
        </div>

        <div class="mb-6 rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-sm text-zinc-400">
            <p class="font-semibold text-white">Rol activo</p>
            <p class="mt-1 text-amber-400">{{ ucfirst($role) }}</p>
        </div>

        <nav class="space-y-2 text-sm">
            <a href="{{ route('dashboard.assets.index') }}" class="block rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 font-semibold text-amber-300 hover:bg-amber-500/20">Panel de activos</a>
            <a href="{{ route('qr.consult') }}" class="block rounded-xl px-4 py-3 text-zinc-300 hover:bg-zinc-900 hover:text-white">Consultar QR</a>
            @if ($role === 'admin')
                <a href="{{ route('dashboard.assets.create') }}" class="block rounded-xl px-4 py-3 text-zinc-300 hover:bg-zinc-900 hover:text-white">Crear activo</a>
                <a href="{{ route('admin.technicians.index') }}" class="block rounded-xl px-4 py-3 text-zinc-300 hover:bg-zinc-900 hover:text-white">Técnicos</a>
            @endif
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="mt-auto border-t border-zinc-800 pt-5">
            @csrf
            <button type="submit" class="w-full rounded-xl border border-red-900 bg-red-950/60 px-4 py-3 text-left text-sm font-semibold text-red-200 hover:bg-red-900">Cerrar sesión</button>
        </form>
    </aside>

    <div class="flex min-h-screen flex-1 flex-col">
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800 bg-black/95 px-4 py-3 text-white backdrop-blur lg:hidden">
            <button class="rounded-lg border border-zinc-700 px-3 py-2 font-semibold" @click="sidebarOpen = true">Menú</button>
            <div class="text-right"><p class="text-xs uppercase tracking-[0.2em] text-amber-400">QRFY</p><p class="title-font font-bold">Panel de activos</p></div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">@yield('content')</main>

        <footer class="border-t border-zinc-800 bg-black/80 px-5 py-5 text-center text-xs text-zinc-500">
            <p>
                © 2026 Todos los derechos reservados para
                <a href="https://gfyservicios.cl" target="_blank" rel="noopener noreferrer" class="text-zinc-300 hover:text-amber-400">gfyservicios.cl</a>
                · QRFY hecho con amor por
                <a href="https://www.area3.cl/" target="_blank" rel="noopener noreferrer" class="text-zinc-300 hover:text-amber-400">Area3.cl</a> 💚
            </p>
            <p class="mt-2"><a href="tel:+56956192168" class="hover:text-amber-400">+56 9 5619 2168</a> · <a href="mailto:contacto@gfyservicios.cl" class="hover:text-amber-400">contacto@gfyservicios.cl</a></p>
        </footer>
    </div>
</div>
</body>
</html>
