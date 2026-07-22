<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'admin@qrfy.local',
        ], [
            'name' => 'Administrador QRFY',
            'password' => Hash::make('Admin1234!'),
            'role' => 'admin',
            'status' => 'activo',
        ]);

        User::updateOrCreate([
            'email' => 'contacto@gfyservicios.cl',
        ], [
            'name' => 'Tecnico Base 1',
            'password' => Hash::make('Tecnico1234!'),
            'role' => 'tecnico',
            'status' => 'activo',
        ]);

        User::updateOrCreate([
            'email' => 'tecnico2@qrfy.local',
        ], [
            'name' => 'Tecnico Base 2',
            'password' => Hash::make('Tecnico1234!'),
            'role' => 'tecnico',
            'status' => 'activo',
        ]);
    }
}
