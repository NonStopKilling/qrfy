@extends('layouts.base')

@section('title', 'Recuperar contraseña | QRFY')

@section('content')
<div class="flex items-center justify-center px-4 py-10 sm:py-14">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white/95 p-8 shadow-xl backdrop-blur">
        <p class="title-font text-xs tracking-[0.25em] text-slate-500 uppercase">Recuperación</p>
        <h1 class="title-font mt-2 text-3xl font-bold text-slate-900">Restablecer acceso</h1>
        <p class="mt-2 text-slate-500">Te enviaremos un enlace para recuperar tu contraseña.</p>

        <form class="mt-8 space-y-4" action="#" method="POST">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Correo registrado</label>
                <input type="email" required placeholder="usuario@empresa.com" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:border-transparent focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-black py-3 font-semibold text-white hover:bg-zinc-800">Enviar enlace</button>
            <a href="{{ route('login') }}" class="block text-center text-sm font-semibold text-[var(--corp-blue)]">Volver al inicio de sesión</a>
        </form>
    </div>
</div>
@endsection
