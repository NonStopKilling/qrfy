@extends('layouts.base')

@section('title', 'Consultar QR | QRFY')

@section('content')
<div class="p-4 sm:p-8" x-data="qrScanner()">
    <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Consulta QR</p>
            <h1 class="title-font mt-1 text-3xl font-bold text-slate-900">Escanear o ingresar código</h1>
            <p class="mt-2 text-slate-500">Busca por código interno, token público o URL completa.</p>

            <div class="mt-5 flex gap-2">
                <button @click="stop(); mode='manual'" type="button" :class="mode==='manual' ? 'bg-[var(--corp-blue)] text-white' : 'bg-slate-100 text-slate-700'" class="rounded-full px-4 py-2 text-sm font-semibold">Manual</button>
                <button @click="mode='camera'" type="button" :class="mode==='camera' ? 'bg-[var(--corp-blue)] text-white' : 'bg-slate-100 text-slate-700'" class="rounded-full px-4 py-2 text-sm font-semibold">Cámara</button>
            </div>

            <form method="GET" action="{{ route('qr.consult') }}" class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Código QR</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input x-ref="code" name="code" type="text" value="{{ $lookupCode }}" placeholder="QR-XXXXXXXX o URL pública" class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:ring-2 focus:ring-[var(--corp-blue)]">
                    <button class="rounded-2xl bg-[var(--corp-blue)] px-5 py-3 font-semibold text-white hover:brightness-90">Consultar</button>
                </div>
            </form>

            <div x-show="mode === 'camera'" x-cloak class="mt-5 rounded-2xl border border-slate-200 p-4">
                <div id="qr-reader" class="min-h-64 w-full overflow-hidden rounded-xl bg-black"></div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <button @click="start" type="button" :disabled="scanning" class="rounded-2xl bg-black px-4 py-3 font-semibold text-white hover:bg-zinc-800 disabled:opacity-50">Iniciar cámara</button>
                    <button @click="stop" type="button" :disabled="!scanning" class="rounded-2xl border border-slate-300 px-4 py-3 font-semibold disabled:opacity-50">Detener</button>
                </div>
                <p x-show="message" x-text="message" class="mt-3 text-sm text-slate-600"></p>
                <p x-show="lastPayload" class="mt-2 break-all text-xs text-slate-500">Última lectura: <span x-text="lastPayload"></span></p>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Resultado</p>
            @if($asset)
                <div class="mx-auto mt-4 max-w-44 overflow-hidden rounded-2xl border border-slate-200">{!! $qrSvg !!}</div>
                <div class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Código</p><p class="font-semibold">{{ $asset->qr_code }}</p></div>
                    <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Equipo</p><p class="font-semibold">{{ $asset->name }}</p></div>
                    <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Estado</p><p class="font-semibold">{{ $asset->status }}</p></div>
                    <a href="{{ route('qr.public', $asset->public_token) }}" class="block rounded-2xl bg-[var(--corp-blue)] px-4 py-3 text-center font-semibold text-white">Abrir ficha pública</a>
                    <a href="{{ route('qr.download', $asset->public_token) }}" class="block rounded-2xl bg-green-600 px-4 py-3 text-center font-semibold text-white hover:bg-green-700">Descargar etiqueta QR</a>
                </div>
            @elseif($lookupCode !== '')
                <div class="mt-4 rounded-2xl border border-dashed border-red-300 bg-red-50 p-6 text-center text-red-700">No existe un activo asociado a ese QR.</div>
            @else
                <div class="mt-4 rounded-2xl border border-dashed border-slate-300 p-6 text-center text-slate-500">Ingresa o escanea un código para consultar.</div>
            @endif
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
function qrScanner() {
    return {
        mode: 'manual', scanning: false, scanner: null,
        message: '', lastPayload: '',
        async start() {
            this.message = '';
            if (!window.Html5Qrcode) {
                this.message = 'No se pudo cargar el escáner. Comprueba la conexión y usa la consulta manual.';
                return;
            }
            try {
                this.scanner = this.scanner || new Html5Qrcode('qr-reader');
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    payload => {
                        this.lastPayload = payload;
                        this.$refs.code.value = payload;
                        this.stop().then(() => this.$refs.code.form.submit());
                    },
                    () => {}
                );
                this.scanning = true;
                this.message = 'Apunta la cámara al código QR.';
            } catch (error) {
                this.message = 'No fue posible abrir la cámara. Revisa el permiso y usa HTTPS o localhost.';
                this.scanning = false;
            }
        },
        async stop() {
            if (!this.scanning || !this.scanner) return;
            this.scanning = false;
            try {
                await this.scanner.stop();
                this.scanner.clear();
            } catch (error) {}
        }
    };
}
</script>
@endsection
