@extends('layouts.base')

@section('title', '404 | QRFY')

@section('content')
<div class="flex items-center justify-center px-4 py-10 sm:py-14">
    <div class="w-full max-w-3xl rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 border border-slate-200">
            <span class="title-font text-3xl font-bold text-slate-900">404</span>
        </div>
        <h1 class="title-font mt-6 text-4xl font-bold text-slate-900">Página no encontrada</h1>
        <p class="mt-3 text-slate-500">El QR no existe, expiró o fue eliminado. El flujo vuelve limpio a la consulta.</p>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('qr.consult') }}" class="rounded-2xl bg-[var(--corp-blue)] px-6 py-3 font-semibold text-white hover:brightness-90">Volver a consultar</a>
            <a href="{{ route('login') }}" class="rounded-2xl bg-black px-6 py-3 font-semibold text-white hover:bg-zinc-800">Ir al inicio de sesión</a>
        </div>
    </div>
</div>
@endsection
