<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            'password' => 'Admin1234!',
            'role' => 'admin',
            'status' => 'activo',
        ]);

        User::updateOrCreate([
            'email' => 'tecnico1@qrfy.local',
        ], [
            'name' => 'Tecnico Base 1',
            'password' => 'Tecnico1234!',
            'role' => 'tecnico',
            'status' => 'activo',
        ]);

        User::updateOrCreate([
            'email' => 'tecnico2@qrfy.local',
        ], [
            'name' => 'Tecnico Base 2',
            'password' => 'Tecnico1234!',
            'role' => 'tecnico',
            'status' => 'activo',
        ]);
    }
}
