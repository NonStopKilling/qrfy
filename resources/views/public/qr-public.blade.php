@extends('layouts.base')

@section('title', 'QR público | QRFY')

@section('content')
<div class="p-4 sm:p-8">
    <div class="mx-auto max-w-4xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
        <div class="bg-black px-6 py-5 text-white">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Vista pública</p>
            <h1 class="title-font mt-1 text-2xl font-bold">Informe de equipo escaneado</h1>
        </div>
        <div class="grid gap-6 p-6 lg:grid-cols-[220px_1fr]">
            <div>
                <div class="aspect-square overflow-hidden rounded-2xl border border-slate-200 shadow-lg">{!! $qrSvg !!}</div>
                <p class="mt-3 text-xs text-slate-500">Código público del activo.</p>
            </div>
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="text-sm text-slate-500">Equipo</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $asset->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="text-sm text-slate-500">Serie</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $asset->serial_number }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="text-sm text-slate-500 mb-3">Estado</p>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                        <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                        {{ $asset->status }}
                    </span>
                </div>
                <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Modelo</p><p class="mt-2 text-slate-700">{{ $asset->model }}</p></div>
                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="text-sm font-semibold text-slate-900">Historial resumido</p>
                    @forelse($asset->maintenances as $maintenance)
                        <div class="mt-3 border-t border-slate-100 pt-3">
                            <p class="text-xs font-semibold text-slate-500">{{ optional($maintenance->performed_at)->format('d-m-Y H:i') ?? 'Sin fecha' }}</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $maintenance->description }}</p>
                        </div>
                    @empty
                        <p class="mt-2 text-sm text-slate-500">Este activo aún no tiene mantenimientos registrados.</p>
                    @endforelse
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('qr.consult') }}" class="rounded-2xl bg-[var(--corp-blue)] px-5 py-3 font-semibold text-white hover:brightness-90">Consultar QR</a>
                    <a href="{{ route('login') }}" class="rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-100">Acceso interno</a>
                    <a href="{{ route('qr.download', $asset->public_token) }}" class="rounded-2xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">Descargar etiqueta QR</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
