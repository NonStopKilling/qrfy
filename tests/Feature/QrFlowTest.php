<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_creation_generates_identifiers_and_redirects_to_real_qr(): void
    {
        $response = $this->post(route('dashboard.assets.store'), [
            'name' => 'Bomba hidráulica',
            'serial_number' => 'SN-1000',
            'model' => 'B17 Pro',
            'status' => 'Operativo',
        ]);

        $asset = Asset::firstOrFail();
        $this->assertMatchesRegularExpression('/^QR-[A-Z0-9]{8}$/', $asset->qr_code);
        $this->assertSame(40, strlen($asset->public_token));
        $response->assertRedirect(route('dashboard.assets.show', ['asset' => $asset, 'role' => 'admin']));

        $this->get(route('dashboard.assets.show', $asset))
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertSee($asset->qr_code);
    }

    public function test_public_route_resolves_token_and_rejects_unknown_token(): void
    {
        $asset = $this->asset();

        $this->get(route('qr.public', $asset->public_token))
            ->assertOk()
            ->assertSee($asset->name)
            ->assertDontSee('Costo');

        $this->get(route('qr.public', 'token-inexistente'))
            ->assertNotFound()
            ->assertSee('Página no encontrada');
    }

    public function test_consultation_finds_by_code_token_and_full_url(): void
    {
        $asset = $this->asset();

        foreach ([$asset->qr_code, $asset->public_token, route('qr.public', $asset->public_token)] as $input) {
            $this->get(route('qr.consult', ['code' => $input]))
                ->assertOk()
                ->assertSee($asset->name)
                ->assertSee($asset->qr_code);
        }
    }

    public function test_editing_asset_keeps_the_public_token(): void
    {
        $asset = $this->asset();
        $token = $asset->public_token;

        $this->put(route('dashboard.assets.update', $asset), [
            'name' => 'Compresor actualizado',
            'serial_number' => 'SN-C18-UPDATED',
            'model' => 'C18 Max 2',
            'status' => 'Revision',
        ])->assertRedirect();

        $asset->refresh();
        $this->assertSame($token, $asset->public_token);
        $this->assertSame('Compresor actualizado', $asset->name);
        $this->get(route('qr.public', $token))->assertOk()->assertSee('Compresor actualizado');
    }

    public function test_duplicate_serial_number_is_rejected(): void
    {
        $this->asset();

        $this->from(route('dashboard.assets.create'))->post(route('dashboard.assets.store'), [
            'name' => 'Otro activo',
            'serial_number' => 'SN-C18-0933',
            'model' => 'Modelo X',
            'status' => 'Operativo',
        ])->assertRedirect(route('dashboard.assets.create'))->assertSessionHasErrors('serial_number');
    }

    public function test_qr_root_and_legacy_urls_redirect_to_spanish_routes(): void
    {
        $this->get('/qr')->assertRedirect('/consulta/qr');
        $this->get('/consult/qr')->assertRedirect('/consulta/qr');
        $this->get('/login')->assertRedirect('/iniciar-sesion');
        $this->get('/dashboard/assets')->assertRedirect('/panel/activos');
    }

    public function test_maintenance_is_saved_for_the_selected_asset(): void
    {
        $asset = $this->asset();

        $this->post(route('dashboard.maintenance.store', $asset), [
            'performed_at' => '2026-06-20 11:30:00',
            'description' => 'Cambio de filtro y prueba general del equipo.',
            'digital_signature' => 'Técnico Interno',
        ])->assertRedirect();

        $this->assertDatabaseHas('maintenance_records', [
            'asset_id' => $asset->id,
            'description' => 'Cambio de filtro y prueba general del equipo.',
        ]);
    }

    public function test_public_sheet_shows_summary_without_private_maintenance_data(): void
    {
        $asset = $this->asset();
        $asset->maintenances()->create([
            'performed_at' => '2026-06-20 11:30:00',
            'description' => 'Lubricación preventiva completada.',
            'before_photo_path' => 'mantenimientos/antes/privada.jpg',
            'after_photo_path' => 'mantenimientos/despues/privada.jpg',
            'digital_signature' => 'Firma Privada',
        ]);

        $this->get(route('qr.public', $asset->public_token))
            ->assertOk()
            ->assertSee('Lubricación preventiva completada.')
            ->assertDontSee('privada.jpg')
            ->assertDontSee('Firma Privada');
    }

    public function test_png_label_can_be_downloaded(): void
    {
        $asset = $this->asset();

        $response = $this->get(route('qr.download', $asset->public_token));

        $response->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Content-Disposition', 'attachment; filename="etiqueta-qr-c4test.png"');
        $png = $response->getContent();
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);
        $size = getimagesizefromstring($png);
        $this->assertIsArray($size);
        $this->assertSame(1800, $size[0]);
        $this->assertSame(2400, $size[1]);

        $image = imagecreatefromstring($png);
        $this->assertInstanceOf(\GdImage::class, $image);
        $this->assertSame([300, 300], imageresolution($image));
        imagedestroy($image);
    }

    public function test_logout_invalidates_authentication_and_uses_spanish_login_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_public_brand_shell_uses_the_real_logo_footer_and_production_domain(): void
    {
        $this->assertSame('https://app.gfyservicios.cl', config('app.url'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('/images/gfyservicios-nuevo-logo.png', false)
            ->assertDontSee('GF<span', false)
            ->assertSee('Todos los derechos reservados')
            ->assertSee('gfyservicios.cl', false)
            ->assertSee('https://www.area3.cl/', false);
    }

    private function asset(): Asset
    {
        return Asset::create([
            'qr_code' => 'QR-C4TEST',
            'name' => 'Compresor C18',
            'serial_number' => 'SN-C18-0933',
            'model' => 'C18 Max',
            'status' => 'Operativo',
            'public_token' => 'public-token-for-tests-123456789012345678',
        ]);
    }
}
