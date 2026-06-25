@extends('layouts.sidebar')

@section('title', 'Técnicos | QRFY')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Admin</p>
                <h2 class="title-font mt-1 text-3xl font-bold text-slate-900">Administración de técnicos</h2>
                <p class="mt-2 text-slate-500">CRUD de usuarios de personal técnico.</p>
            </div>
            <button class="rounded-2xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">+ Crear nuevo técnico</button>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-slate-500">
                        <th class="py-3 pr-3">Nombre</th>
                        <th class="py-3 pr-3">Correo</th>
                        <th class="py-3 pr-3">Estado</th>
                        <th class="py-3 pr-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($technicians as $technician)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                            <td class="py-4 pr-3 font-semibold text-slate-800">{{ $technician['name'] }}</td>
                            <td class="py-4 pr-3 text-slate-600">{{ $technician['email'] }}</td>
                            <td class="py-4 pr-3"><span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $technician['status'] === 'Activo' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">{{ $technician['status'] }}</span></td>
                            <td class="py-4 pr-3">
                                <div class="flex gap-2">
                                    <button class="rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">Ver</button>
                                    <button class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600">Editar</button>
                                    <button class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Borrar</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
