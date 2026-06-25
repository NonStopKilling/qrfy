@extends('layouts.base')

@section('title', 'Iniciar sesión | QRFY')

@section('content')
<div class="flex items-center justify-center px-4 py-10 sm:py-14">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white/95 p-8 shadow-xl backdrop-blur">
        <div class="text-center">
            <p class="title-font text-xs tracking-[0.25em] text-slate-500 uppercase">Gestión de activos</p>
            <h1 class="title-font mt-2 text-3xl font-bold text-slate-900">Iniciar sesión</h1>
            <p class="mt-2 text-slate-500">Acceso técnico y administrativo</p>
        </div>

        <form class="mt-8 space-y-4" action="{{ route('login.submit') }}" method="POST">
            @csrf
            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Correo</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="tecnico@empresa.com" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:border-transparent focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Contraseña</label>
                <input type="password" name="password" required minlength="8" placeholder="********" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:border-transparent focus:ring-2 focus:ring-[var(--corp-blue)]">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2 text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                    Recordarme
                </label>
                <a href="{{ route('password.request') }}" class="font-semibold text-[var(--corp-blue)]">Recuperar contraseña</a>
            </div>
            <button type="submit" class="w-full rounded-2xl bg-[var(--corp-blue)] py-3 font-semibold text-white hover:brightness-90">Entrar</button>
        </form>

        <p class="mt-6 text-center text-xs text-slate-500">Timeout de sesión sugerido: 60 minutos.</p>
    </div>
</div>
@endsection
