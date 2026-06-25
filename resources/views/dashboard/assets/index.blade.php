@extends('layouts.sidebar')

@section('title', 'Activos | QRFY')

@section('content')
@php $role = $role ?? 'tecnico'; @endphp
<div x-data="{ query: '', status: 'all' }" class="space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-6 shadow-xl">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Panel de control</p>
                <h2 class="title-font mt-1 text-3xl font-bold text-slate-900">Listado de activos</h2>
                <p class="mt-2 text-slate-500">Filtro básico, estado visual y gestión de etiquetas QR.</p>
            </div>
            <a href="{{ route('dashboard.assets.create') }}" class="rounded-2xl bg-green-600 px-5 py-3 font-semibold text-white hover:bg-green-700">+ Crear QR</a>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <input x-model="query" type="text" placeholder="Buscar equipo, serie o QR" class="sm:col-span-2 rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
            <select x-model="status" class="rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
                <option value="all">Todos</option>
                <option value="Operativo">Operativo</option>
                <option value="Revision">Revisión</option>
                <option value="Fuera de servicio">Fuera de servicio</option>
            </select>
        </div>

        @if (session('success'))
            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-slate-500">
                        <th class="py-3 pr-3">Código QR</th>
                        <th class="py-3 pr-3">Equipo</th>
                        <th class="py-3 pr-3">Serie</th>
                        <th class="py-3 pr-3">Última mantención</th>
                        <th class="py-3 pr-3">Estado</th>
                        <th class="py-3 pr-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $asset)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80" x-show="('{{ strtolower($asset->qr_code) }}'.includes(query.toLowerCase()) || '{{ strtolower($asset->name) }}'.includes(query.toLowerCase()) || '{{ strtolower($asset->serial_number) }}'.includes(query.toLowerCase())) && (status === 'all' || status === '{{ $asset->status }}')">
                            <td class="py-4 pr-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 overflow-hidden rounded-lg border border-slate-200">{!! $asset->qr_svg !!}</div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $asset->qr_code }}</p>
                                        <p class="text-[11px] text-slate-500">QR activo</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 pr-3 font-semibold text-slate-800">{{ $asset->name }}</td>
                            <td class="py-4 pr-3 text-slate-600">{{ $asset->serial_number }}</td>
                            <td class="py-4 pr-3 text-slate-600">{{ $asset->maintenances_max_performed_at ? \Illuminate\Support\Carbon::parse($asset->maintenances_max_performed_at)->format('Y-m-d') : 'Sin registro' }}</td>
                            <td class="py-4 pr-3">
                                @php
                                    $badge = match($asset->status) {
                                        'Operativo' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Revision' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        default => 'bg-red-50 text-red-700 border-red-200',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $asset->status }}</span>
                            </td>
                            <td class="py-4 pr-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('dashboard.assets.show', ['asset' => $asset, 'role' => $role]) }}" class="rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-700">Ver</a>
                                    <a href="{{ route('dashboard.assets.edit', ['asset' => $asset, 'role' => $role]) }}" class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-semibold text-white hover:bg-amber-600">Editar</a>
                                    <a href="{{ route('dashboard.assets.delete', ['asset' => $asset, 'role' => $role]) }}" class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Borrar</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($assets->isEmpty())
                        <tr><td colspan="6" class="py-10 text-center text-slate-500">Aún no hay activos. Crea el primero para generar su QR.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
