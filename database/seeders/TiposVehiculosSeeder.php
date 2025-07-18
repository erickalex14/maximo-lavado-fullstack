<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposVehiculosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiposVehiculos = [
            [
                'nombre' => 'moto',
                'descripcion' => 'Motocicletas y motonetas',
                'requiere_matricula' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'auto_pequeno',
                'descripcion' => 'Automóviles pequeños (sedan, hatchback pequeño)',
                'requiere_matricula' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'auto_mediano',
                'descripcion' => 'Automóviles medianos (SUV pequeño, sedan mediano)',
                'requiere_matricula' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'camioneta',
                'descripcion' => 'Camionetas, SUV grandes, pick-up',
                'requiere_matricula' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('tipos_vehiculos')->insert($tiposVehiculos);
    }
}
