@extends('layouts.sidebar')

@section('title', 'Eliminar activo | QRFY')

@section('content')
<div class="mx-auto max-w-3xl">
    <section class="rounded-3xl border border-red-300 bg-white p-8 text-center shadow-xl">
        <p class="text-xs uppercase tracking-[0.2em] text-red-500">Eliminar</p>
        <h2 class="title-font mt-1 text-3xl font-bold text-red-900">Eliminar equipo</h2>
        <p class="mt-3 text-slate-600">Esta acción no se puede deshacer.</p>

        <div class="mx-auto mt-6 max-w-md rounded-2xl border border-red-200 bg-red-50 p-4 text-left text-sm text-red-700">
            <p class="font-semibold">{{ $asset->name }}</p>
            <p>{{ $asset->serial_number }}</p>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('dashboard.assets.index', ['role' => $role]) }}" class="rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-100">Cancelar</a>
            <form method="POST" action="{{ route('dashboard.assets.destroy', ['asset' => $asset, 'role' => $role]) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-2xl bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-700">Eliminar activo</button>
            </form>
        </div>
    </section>
</div>
@endsection
