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
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Crear usuario normal para testing
        User::create([
            'name' => 'Usuario Test',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
        ]);

        echo "Usuarios de prueba creados:\n";
        echo "- admin@test.com / password123\n";
        echo "- user@test.com / password123\n";
    }
}
