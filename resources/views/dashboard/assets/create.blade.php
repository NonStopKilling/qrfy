@extends('layouts.sidebar')

@section('title', 'Crear QR | QRFY')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Crear</p>
        <h2 class="title-font mt-1 text-3xl font-bold text-slate-900">Crear QR y dispositivo</h2>
        <p class="mt-2 text-slate-500">Alta de activo con asociación automática del código QR.</p>

        @if($errors->any())
            <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('dashboard.assets.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Equipo</label>
                <input name="name" value="{{ old('name') }}" type="text" required minlength="3" placeholder="Nombre del equipo" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">N° de serie</label>
                <input name="serial_number" value="{{ old('serial_number') }}" type="text" required placeholder="SN-XXXX-0000" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Modelo</label>
                <input name="model" value="{{ old('model') }}" type="text" required minlength="2" placeholder="Modelo del equipo" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Estado</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3"><option>Operativo</option><option>Revision</option><option>Fuera de servicio</option></select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Manual PDF</label>
                <input name="manual_pdf" type="file" accept="application/pdf" class="w-full rounded-2xl border border-slate-300 px-4 py-3 bg-white">
            </div>
            <div class="sm:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="rounded-2xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">+ Generar y asociar QR</button>
                <a href="{{ route('dashboard.assets.index', ['role' => 'admin']) }}" class="rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-100">Volver</a>
            </div>
        </form>
    </section>

</div>
@endsection
