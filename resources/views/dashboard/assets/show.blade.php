@extends('layouts.sidebar')

@section('title', 'Detalle activo | QRFY')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="grid gap-6 lg:grid-cols-[220px_1fr]">
            <div>
                <div class="aspect-square overflow-hidden rounded-2xl border border-slate-200">{!! $qrSvg !!}</div>
                <p class="mt-3 text-xs text-slate-500">Escanea para abrir la ficha pública.</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Vista del activo</p>
                <h2 class="title-font mt-1 text-3xl font-bold text-slate-900">{{ $asset->name }}</h2>
                <p class="mt-2 text-slate-500">Ficha técnica completa y resumida.</p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">QR</p><p class="font-semibold">{{ $asset->qr_code }}</p></div>
                    <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Serie</p><p class="font-semibold">{{ $asset->serial_number }}</p></div>
                    <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Modelo</p><p class="font-semibold">{{ $asset->model }}</p></div>
                    <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Estado</p><p class="font-semibold">{{ $asset->status }}</p></div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('dashboard.assets.index', ['role' => $role]) }}" class="rounded-2xl bg-slate-600 px-5 py-3 font-semibold text-white hover:bg-slate-700">Volver</a>
            <a href="{{ route('dashboard.assets.edit', ['asset' => $asset, 'role' => $role]) }}" class="rounded-2xl bg-amber-500 px-5 py-3 font-semibold text-white hover:bg-amber-600">Editar</a>
            <a href="{{ route('qr.public', $asset->public_token) }}" target="_blank" class="rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-100">Abrir ficha pública</a>
            <a href="{{ route('qr.download', $asset->public_token) }}" class="rounded-2xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">Descargar etiqueta QR</a>
            <a href="{{ route('dashboard.maintenance.show', ['asset' => $asset, 'role' => $role]) }}" class="rounded-2xl bg-[var(--corp-blue)] px-5 py-3 font-semibold text-white hover:brightness-90">Hoja de mantenimiento</a>
        </div>
    </section>
</div>
@endsection
