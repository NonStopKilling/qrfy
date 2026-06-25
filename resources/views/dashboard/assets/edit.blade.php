@extends('layouts.sidebar')

@section('title', 'Editar activo | QRFY')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Actualizar</p>
        <h2 class="title-font mt-1 text-3xl font-bold text-slate-900">Editar equipo</h2>
        <p class="mt-2 text-slate-500">Campos validados para actualizar el activo.</p>

        @if($errors->any())
            <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('dashboard.assets.update', ['asset' => $asset, 'role' => $role]) }}" enctype="multipart/form-data" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Código QR</label>
                <input type="text" disabled value="{{ $asset->qr_code }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Equipo</label>
                <input name="name" type="text" required minlength="3" value="{{ old('name', $asset->name) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Serie</label>
                <input name="serial_number" type="text" required value="{{ old('serial_number', $asset->serial_number) }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Estado</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
                    @foreach(['Operativo', 'Revision', 'Fuera de servicio'] as $status)<option @selected(old('status', $asset->status) === $status)>{{ $status }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Modelo</label>
                <input name="model" value="{{ old('model', $asset->model) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Reemplazar manual PDF</label>
                <input name="manual_pdf" type="file" accept="application/pdf" class="w-full rounded-2xl border border-slate-300 px-4 py-3">
            </div>
            <div class="sm:col-span-2 flex flex-wrap gap-3">
                <a href="{{ route('dashboard.assets.show', ['asset' => $asset, 'role' => $role]) }}" class="rounded-2xl bg-slate-600 px-5 py-3 font-semibold text-white hover:bg-slate-700">Cancelar</a>
                <button type="submit" class="rounded-2xl bg-[var(--corp-blue)] px-5 py-3 font-semibold text-white hover:brightness-90">Guardar cambios</button>
            </div>
        </form>
    </section>
</div>
@endsection
