@extends('layouts.sidebar')

@section('title', 'Editar técnico | QRFY')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Admin</p>
        <h2 class="title-font mt-1 text-3xl font-bold text-slate-900">Editar técnico</h2>
        <p class="mt-2 text-slate-500">Actualiza datos de acceso y estado del usuario técnico.</p>

        @if($errors->any())
            <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.technicians.update', $technician) }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nombre</label>
                <input name="name" value="{{ old('name', $technician->name) }}" type="text" required minlength="3" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Correo</label>
                <input name="email" value="{{ old('email', $technician->email) }}" type="email" required class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                <input name="password" type="password" minlength="8" placeholder="Dejar en blanco para conservar" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Confirmar nueva contraseña</label>
                <input name="password_confirmation" type="password" minlength="8" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Estado</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
                    <option value="activo" @selected(old('status', $technician->status) === 'activo')>Activo</option>
                    <option value="suspendido" @selected(old('status', $technician->status) === 'suspendido')>Suspendido</option>
                </select>
            </div>
            <div class="sm:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="rounded-2xl bg-[var(--corp-blue)] px-5 py-3 font-semibold text-white hover:brightness-90">Guardar cambios</button>
                <a href="{{ route('admin.technicians.index') }}" class="rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-100">Cancelar</a>
            </div>
        </form>
    </section>
</div>
@endsection
