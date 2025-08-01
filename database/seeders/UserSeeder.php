<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador para testing
        User::firstOrCreate(
            ['email' => 'admin@lavado.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@lavado.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Usuario administrador creado:');
        $this->command->line('� Email: admin@lavado.com');
        $this->command->line('🔒 Password: password123');
    }
}
