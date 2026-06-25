@extends('layouts.sidebar')

@section('title', 'Crear técnico | QRFY')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Admin</p>
        <h2 class="title-font mt-1 text-3xl font-bold text-slate-900">Crear técnico</h2>
        <p class="mt-2 text-slate-500">Alta de usuario técnico con contraseña definida por administración.</p>

        @if($errors->any())
            <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.technicians.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nombre</label>
                <input name="name" value="{{ old('name') }}" type="text" required minlength="3" placeholder="Nombre del técnico" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Correo</label>
                <input name="email" value="{{ old('email') }}" type="email" required placeholder="tecnico@empresa.com" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Contraseña</label>
                <input name="password" type="password" required minlength="8" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
                <input name="password_confirmation" type="password" required minlength="8" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Estado</label>
                <select name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
                    <option value="activo" @selected(old('status', 'activo') === 'activo')>Activo</option>
                    <option value="suspendido" @selected(old('status') === 'suspendido')>Suspendido</option>
                </select>
            </div>
            <div class="sm:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="rounded-2xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">Crear técnico</button>
                <a href="{{ route('admin.technicians.index') }}" class="rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-100">Cancelar</a>
            </div>
        </form>
    </section>
</div>
@endsection
