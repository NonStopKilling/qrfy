<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Services\QrCodeSvgGenerator;
use App\Services\QrLabelPngGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class PageController extends Controller
{
    public function __construct(
        private readonly QrCodeSvgGenerator $qrGenerator,
        private readonly QrLabelPngGenerator $labelGenerator,
    ) {}

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.assets.index', ['role' => Auth::user()->role ?? 'tecnico']);
        }

        return view('auth.login');
    }

    public function loginSubmit(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);
        $remember = (bool) ($credentials['remember'] ?? false);

        try {
            if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'status' => 'activo'], $remember)) {
                return back()->withErrors(['email' => 'Credenciales invalidas.'])->onlyInput('email');
            }
        } catch (RuntimeException $exception) {
            Log::warning('Intento de login con hash de password invalido.', [
                'email' => $credentials['email'],
                'error' => $exception->getMessage(),
            ]);

            return back()->withErrors(['email' => 'Credenciales invalidas.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard.assets.index', ['role' => Auth::user()->role ?? 'tecnico']);
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function qrConsult(Request $request)
    {
        $lookupCode = trim((string) $request->query('code', ''));
        $asset = $lookupCode === '' ? null : $this->findAsset($lookupCode);
        $qrSvg = $asset ? $this->assetQr($asset, 4) : null;

        return view('public.qr-consult', compact('asset', 'lookupCode', 'qrSvg'));
    }

    public function qrPublic(string $token)
    {
        $asset = Asset::where('public_token', $token)
            ->with(['maintenances' => fn ($query) => $query
                ->select(['id', 'asset_id', 'performed_at', 'description', 'before_photo_path', 'after_photo_path', 'maintenance_pdf_path', 'digital_signature'])
                ->latest('performed_at')])
            ->first();
        if (! $asset) {
            return response()->view('public.not-found', [], 404);
        }

        return view('public.qr-public', [
            'asset' => $asset,
            'qrSvg' => $this->assetQr($asset),
        ]);
    }

    public function qrDownload(string $token)
    {
        $asset = Asset::where('public_token', $token)->first();
        if (! $asset) {
            return response()->view('public.not-found', [], 404);
        }

        try {
            $png = $this->labelGenerator->generate(
                $asset,
                $this->publicQrUrl($asset),
                $this->canonicalUrl(route('qr.consult', absolute: false)),
            );

            return response($png, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="etiqueta-'.strtolower($asset->qr_code).'.png"',
                'Content-Length' => (string) strlen($png),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Fallo generación PNG de etiqueta QR, se entrega SVG de respaldo.', [
                'asset_id' => $asset->id,
                'qr_code' => $asset->qr_code,
                'error' => $exception->getMessage(),
            ]);

            $svg = $this->assetQr($asset, 8);

            return response($svg, 200, [
                'Content-Type' => 'image/svg+xml; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="etiqueta-'.strtolower($asset->qr_code).'.svg"',
                'Content-Length' => (string) strlen($svg),
            ]);
        }
    }

    public function qrManualDownload(string $token)
    {
        $asset = Asset::where('public_token', $token)->first();
        if (! $asset || ! $asset->manual_pdf_path) {
            return response()->view('public.not-found', [], 404);
        }

        return $this->downloadPublicDiskFile(
            $asset->manual_pdf_path,
            'manual-'.strtolower($asset->qr_code).'.pdf',
            'application/pdf',
        );
    }

    public function qrMaintenanceFileDownload(string $token, int $maintenance, string $type)
    {
        $asset = Asset::where('public_token', $token)->first();
        if (! $asset) {
            return response()->view('public.not-found', [], 404);
        }

        $record = $asset->maintenances()
            ->select(['id', 'before_photo_path', 'after_photo_path', 'maintenance_pdf_path'])
            ->find($maintenance);
        if (! $record) {
            return response()->view('public.not-found', [], 404);
        }

        $mapping = [
            'before' => [
                'path' => $record->before_photo_path,
                'name' => 'foto-antes-'.$record->id.'-'.strtolower($asset->qr_code),
                'mime' => null,
            ],
            'after' => [
                'path' => $record->after_photo_path,
                'name' => 'foto-despues-'.$record->id.'-'.strtolower($asset->qr_code),
                'mime' => null,
            ],
            'pdf' => [
                'path' => $record->maintenance_pdf_path,
                'name' => 'mantenimiento-'.$record->id.'-'.strtolower($asset->qr_code).'.pdf',
                'mime' => 'application/pdf',
            ],
        ];

        $file = $mapping[$type] ?? null;
        if (! $file || ! is_string($file['path']) || $file['path'] === '') {
            return response()->view('public.not-found', [], 404);
        }

        return $this->downloadPublicDiskFile($file['path'], $file['name'], $file['mime']);
    }

    public function notFound()
    {
        return response()->view('public.not-found', [], 404);
    }

    public function dashboardIndex(Request $request)
    {
        $assets = Asset::withMax('maintenances', 'performed_at')->latest()->get();
        $assets->each(fn (Asset $asset) => $asset->qr_svg = $this->assetQr($asset, 2));

        return view('dashboard.assets.index', [
            'assets' => $assets,
            'role' => $request->query('role', 'tecnico'),
        ]);
    }

    public function assetShow(Request $request, Asset $asset)
    {
        $asset->loadMax('maintenances', 'performed_at');

        return view('dashboard.assets.show', [
            'asset' => $asset,
            'role' => $request->query('role', 'tecnico'),
            'qrSvg' => $this->assetQr($asset),
        ]);
    }

    public function assetEdit(Request $request, Asset $asset)
    {
        return view('dashboard.assets.edit', [
            'asset' => $asset,
            'role' => $request->query('role', 'tecnico'),
        ]);
    }

    public function assetDelete(Request $request, Asset $asset)
    {
        return view('dashboard.assets.delete', [
            'asset' => $asset,
            'role' => $request->query('role', 'tecnico'),
        ]);
    }

    public function assetDestroy(Request $request, Asset $asset)
    {
        $role = $request->query('role', Auth::user()->role ?? 'tecnico');
        $asset->delete();

        return redirect()
            ->route('dashboard.assets.index', ['role' => $role])
            ->with('success', 'Activo eliminado correctamente.');
    }

    public function assetCreate()
    {
        return view('dashboard.assets.create', ['role' => 'admin']);
    }

    public function assetStore(Request $request)
    {
        $validated = $this->validateAsset($request);
        if ($request->hasFile('manual_pdf')) {
            $validated['manual_pdf_path'] = $request->file('manual_pdf')->store('manuals', 'public');
        }
        $validated['qr_code'] = $this->uniqueValue('qr_code', fn () => 'QR-'.strtoupper(Str::random(8)));
        $validated['public_token'] = $this->uniqueValue('public_token', fn () => Str::random(40));
        $asset = Asset::create($validated);

        return redirect()->route('dashboard.assets.show', ['asset' => $asset, 'role' => 'admin'])
            ->with('success', 'Activo y código QR creados correctamente.');
    }

    public function assetUpdate(Request $request, Asset $asset)
    {
        $validated = $this->validateAsset($request, $asset);
        if ($request->hasFile('manual_pdf')) {
            $validated['manual_pdf_path'] = $request->file('manual_pdf')->store('manuals', 'public');
        }
        $asset->update($validated);

        return redirect()->route('dashboard.assets.show', [
            'asset' => $asset,
            'role' => $request->query('role', 'tecnico'),
        ])->with('success', 'Ficha actualizada. El QR impreso sigue siendo válido.');
    }

    public function maintenanceShow(Request $request, Asset $asset)
    {
        $asset->load(['maintenances' => fn ($query) => $query->latest('performed_at')]);

        return view('dashboard.maintenance.show', [
            'role' => $request->query('role', 'tecnico'),
            'asset' => $asset,
        ]);
    }

    public function maintenanceStore(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'performed_at' => ['required', 'date'],
            'description' => ['required', 'string', 'min:12', 'max:5000'],
            'before_photo' => ['nullable', 'image', 'max:5120'],
            'after_photo' => ['nullable', 'image', 'max:5120'],
            'maintenance_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'digital_signature' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('before_photo')) {
            $validated['before_photo_path'] = $request->file('before_photo')->store('mantenimientos/antes', 'public');
        }
        if ($request->hasFile('after_photo')) {
            $validated['after_photo_path'] = $request->file('after_photo')->store('mantenimientos/despues', 'public');
        }
        if ($request->hasFile('maintenance_pdf')) {
            $validated['maintenance_pdf_path'] = $request->file('maintenance_pdf')->store('mantenimientos/pdfs', 'public');
        }
        unset($validated['before_photo'], $validated['after_photo'], $validated['maintenance_pdf']);
        $validated['user_id'] = Auth::id();

        $asset->maintenances()->create($validated);

        return redirect()->route('dashboard.maintenance.show', [
            'asset' => $asset,
            'role' => $request->query('role', 'tecnico'),
        ])->with('success', 'Mantenimiento vinculado al QR del activo.');
    }

    public function techniciansIndex()
    {
        return view('admin.technicians.index', [
            'role' => 'admin',
            'technicians' => User::where('role', 'tecnico')->latest()->get(),
        ]);
    }

    public function technicianCreate()
    {
        return view('admin.technicians.create', ['role' => 'admin']);
    }

    public function technicianStore(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['activo', 'suspendido'])],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'tecnico',
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.technicians.index')
            ->with('success', 'Técnico creado correctamente.');
    }

    public function technicianEdit(User $user)
    {
        abort_unless($user->role === 'tecnico', 404);

        return view('admin.technicians.edit', ['role' => 'admin', 'technician' => $user]);
    }

    public function technicianUpdate(Request $request, User $user)
    {
        abort_unless($user->role === 'tecnico', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['activo', 'suspendido'])],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.technicians.index')
            ->with('success', 'Técnico actualizado correctamente.');
    }

    public function technicianDelete(User $user)
    {
        abort_unless($user->role === 'tecnico', 404);

        return view('admin.technicians.delete', ['role' => 'admin', 'technician' => $user]);
    }

    public function technicianDestroy(User $user)
    {
        abort_unless($user->role === 'tecnico', 404);

        $user->delete();

        return redirect()
            ->route('admin.technicians.index')
            ->with('success', 'Técnico eliminado correctamente.');
    }

    private function findAsset(string $input): ?Asset
    {
        $candidate = $input;
        $path = parse_url($input, PHP_URL_PATH);
        if (is_string($path) && preg_match('~/qr/([^/]+)~', $path, $matches)) {
            $candidate = urldecode($matches[1]);
        }

        return Asset::where('public_token', $candidate)
            ->orWhere('qr_code', strtoupper($candidate))
            ->first();
    }

    private function validateAsset(Request $request, ?Asset $asset = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'serial_number' => ['required', 'string', 'max:191', Rule::unique('assets')->ignore($asset)],
            'model' => ['required', 'string', 'min:2', 'max:255'],
            'status' => ['required', Rule::in(['Operativo', 'Revision', 'Fuera de servicio'])],
            'manual_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);
    }

    private function uniqueValue(string $column, callable $generate): string
    {
        do {
            $value = $generate();
        } while (Asset::where($column, $value)->exists());

        return $value;
    }

    private function assetQr(Asset $asset, int $scale = 6): string
    {
        return $this->qrGenerator->generate($this->publicQrUrl($asset), $scale);
    }

    private function publicQrUrl(Asset $asset): string
    {
        return $this->canonicalUrl(route('qr.public', $asset->public_token, false));
    }

    private function canonicalUrl(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    private function downloadPublicDiskFile(string $storagePath, string $downloadName, ?string $mimeType = null)
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($storagePath)) {
            return response()->view('public.not-found', [], 404);
        }

        $absolutePath = $disk->path($storagePath);
        $filename = str_contains($downloadName, '.') ? $downloadName : basename($storagePath);

        return response()->download($absolutePath, $filename, $mimeType ? ['Content-Type' => $mimeType] : []);
    }
}
