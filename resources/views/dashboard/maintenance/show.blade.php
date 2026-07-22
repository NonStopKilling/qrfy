@extends('layouts.sidebar')

@section('title', 'Mantenimiento | QRFY')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Mantenimiento vinculado al QR</p>
        <h2 class="title-font mt-1 text-3xl font-bold text-slate-900">Hoja de vida: {{ $asset->name }}</h2>
        <p class="mt-2 text-slate-500">{{ $asset->qr_code }} · Serie {{ $asset->serial_number }}</p>

        @if(session('success'))<div class="mt-5 rounded-2xl border border-green-200 bg-green-50 p-4 text-green-700">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">{{ $errors->first() }}</div>@endif

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="space-y-3">
                <div class="rounded-2xl border border-slate-200 p-4">
                    <p class="text-sm font-semibold text-slate-900">Historial</p>
                    <ol class="mt-3 space-y-3">
                        @forelse($asset->maintenances as $maintenance)
                            <li class="rounded-xl border border-slate-200 p-3">
                                <p class="text-xs font-semibold text-slate-500">{{ optional($maintenance->performed_at)?->timezone(config('app.timezone'))->format('d-m-Y H:i') }} CL</p>
                                <p class="mt-1 text-slate-700">{{ $maintenance->description }}</p>
                                @if($maintenance->digital_signature)<p class="mt-2 text-xs text-slate-500">Firma: {{ $maintenance->digital_signature }}</p>@endif
                                @if($maintenance->before_photo_path || $maintenance->after_photo_path || $maintenance->maintenance_pdf_path)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if($maintenance->before_photo_path)
                                            <a href="{{ route('qr.maintenance.download', ['token' => $asset->public_token, 'maintenance' => $maintenance->id, 'type' => 'before']) }}" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Descargar foto antes</a>
                                        @endif
                                        @if($maintenance->after_photo_path)
                                            <a href="{{ route('qr.maintenance.download', ['token' => $asset->public_token, 'maintenance' => $maintenance->id, 'type' => 'after']) }}" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">Descargar foto después</a>
                                        @endif
                                        @if($maintenance->maintenance_pdf_path)
                                            <a href="{{ route('qr.maintenance.download', ['token' => $asset->public_token, 'maintenance' => $maintenance->id, 'type' => 'pdf']) }}" class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Descargar PDF</a>
                                        @endif
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-slate-500">Sin mantenimientos registrados.</li>
                        @endforelse
                    </ol>
                </div>
            </div>

            <form method="POST" action="{{ route('dashboard.maintenance.store', ['asset' => $asset, 'role' => $role]) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div><label class="mb-2 block text-sm font-semibold text-slate-700">Fecha y hora</label><input name="performed_at" type="datetime-local" value="{{ old('performed_at', now(config('app.timezone'))->format('Y-m-d\TH:i')) }}" required class="w-full rounded-2xl border border-slate-300 px-4 py-3"></div>
                <div><label class="mb-2 block text-sm font-semibold text-slate-700">Descripción técnica</label><textarea name="description" rows="5" required minlength="12" class="w-full rounded-2xl border border-slate-300 px-4 py-3" placeholder="Detalle del trabajo realizado">{{ old('description') }}</textarea></div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div><label class="mb-2 block text-sm font-semibold text-slate-700">Foto antes</label><input name="before_photo" type="file" accept="image/*" class="w-full rounded-2xl border border-slate-300 px-3 py-2"></div>
                    <div><label class="mb-2 block text-sm font-semibold text-slate-700">Foto después</label><input name="after_photo" type="file" accept="image/*" class="w-full rounded-2xl border border-slate-300 px-3 py-2"></div>
                </div>
                <div><label class="mb-2 block text-sm font-semibold text-slate-700">PDF de mantención</label><input name="maintenance_pdf" type="file" accept="application/pdf" class="w-full rounded-2xl border border-slate-300 px-3 py-2"></div>
                <div><label class="mb-2 block text-sm font-semibold text-slate-700">Firma digital</label><input name="digital_signature" value="{{ old('digital_signature') }}" type="text" class="w-full rounded-2xl border border-slate-300 px-4 py-3" placeholder="Nombre completo"></div>
                <div class="flex flex-wrap gap-3"><button class="rounded-2xl bg-[var(--corp-blue)] px-5 py-3 font-semibold text-white hover:brightness-90">Guardar mantenimiento</button><a href="{{ route('dashboard.assets.show', ['asset' => $asset, 'role' => $role]) }}" class="rounded-2xl border border-slate-300 px-5 py-3 font-semibold">Volver al activo</a></div>
            </form>
        </div>
    </section>
</div>
@endsection
