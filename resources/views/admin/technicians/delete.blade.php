@extends('layouts.sidebar')

@section('title', 'Eliminar técnico | QRFY')

@section('content')
<div class="mx-auto max-w-3xl">
    <section class="rounded-3xl border border-red-300 bg-white p-8 text-center shadow-xl">
        <p class="text-xs uppercase tracking-[0.2em] text-red-500">Eliminar</p>
        <h2 class="title-font mt-1 text-3xl font-bold text-red-900">Eliminar técnico</h2>
        <p class="mt-3 text-slate-600">Esta acción no elimina los mantenimientos ya registrados; quedarán sin usuario asociado.</p>

        <div class="mx-auto mt-6 max-w-md rounded-2xl border border-red-200 bg-red-50 p-4 text-left text-sm text-red-700">
            <p class="font-semibold">{{ $technician->name }}</p>
            <p>{{ $technician->email }}</p>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('admin.technicians.index') }}" class="rounded-2xl border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-100">Cancelar</a>
            <form method="POST" action="{{ route('admin.technicians.destroy', $technician) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-2xl bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-700">Eliminar técnico</button>
            </form>
        </div>
    </section>
</div>
@endsection
