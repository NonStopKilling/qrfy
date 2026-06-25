<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/iniciar-sesion');

Route::get('/iniciar-sesion', [PageController::class, 'login'])->name('login');
Route::post('/iniciar-sesion', [PageController::class, 'loginSubmit'])->name('login.submit');
Route::post('/cerrar-sesion', [PageController::class, 'logout'])->name('logout');
Route::get('/recuperar-clave', [PageController::class, 'forgotPassword'])->name('password.request');

Route::get('/consulta/qr', [PageController::class, 'qrConsult'])->name('qr.consult');
Route::redirect('/qr', '/consulta/qr');
Route::get('/qr/{token}/descargar', [PageController::class, 'qrDownload'])->name('qr.download');
Route::get('/qr/{token}', [PageController::class, 'qrPublic'])->name('qr.public');
Route::get('/404', [PageController::class, 'notFound'])->name('not-found');

Route::get('/panel/activos', [PageController::class, 'dashboardIndex'])->name('dashboard.assets.index');
Route::get('/panel/activos/crear', [PageController::class, 'assetCreate'])->name('dashboard.assets.create');
Route::post('/panel/activos', [PageController::class, 'assetStore'])->name('dashboard.assets.store');
Route::get('/panel/activos/{asset}', [PageController::class, 'assetShow'])->name('dashboard.assets.show');
Route::get('/panel/activos/{asset}/editar', [PageController::class, 'assetEdit'])->name('dashboard.assets.edit');
Route::put('/panel/activos/{asset}', [PageController::class, 'assetUpdate'])->name('dashboard.assets.update');
Route::get('/panel/activos/{asset}/eliminar', [PageController::class, 'assetDelete'])->name('dashboard.assets.delete');
Route::delete('/panel/activos/{asset}', [PageController::class, 'assetDestroy'])->name('dashboard.assets.destroy');
Route::get('/panel/activos/{asset}/mantenimiento', [PageController::class, 'maintenanceShow'])->name('dashboard.maintenance.show');
Route::post('/panel/activos/{asset}/mantenimientos', [PageController::class, 'maintenanceStore'])->name('dashboard.maintenance.store');
Route::get('/administracion/tecnicos', [PageController::class, 'techniciansIndex'])->name('admin.technicians.index');

Route::redirect('/login', '/iniciar-sesion');
Route::redirect('/forgot-password', '/recuperar-clave');
Route::redirect('/consult/qr', '/consulta/qr');
Route::redirect('/dashboard/assets', '/panel/activos');
Route::redirect('/dashboard/assets/create', '/panel/activos/crear');
Route::get('/dashboard/assets/{asset}/edit', fn (string $asset) => redirect('/panel/activos/'.$asset.'/editar'));
Route::get('/dashboard/assets/{asset}/delete', fn (string $asset) => redirect('/panel/activos/'.$asset.'/eliminar'));
Route::get('/dashboard/assets/{asset}', fn (string $asset) => redirect('/panel/activos/'.$asset));
Route::redirect('/dashboard/maintenance', '/panel/activos');
Route::redirect('/admin/technicians', '/administracion/tecnicos');
