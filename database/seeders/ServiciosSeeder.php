<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los IDs de tipos de vehículos
        $tipoMoto = DB::table('tipos_vehiculos')->where('nombre', 'moto')->first();
        $tipoAutoPequeno = DB::table('tipos_vehiculos')->where('nombre', 'auto_pequeno')->first();
        $tipoAutoMediano = DB::table('tipos_vehiculos')->where('nombre', 'auto_mediano')->first();
        $tipoCamioneta = DB::table('tipos_vehiculos')->where('nombre', 'camioneta')->first();

        $servicios = [
            // Servicios para MOTOS
            [
                'nombre' => 'Lavado Completo',
                'descripcion' => 'Lavado completo interior y exterior para motocicletas',
                'tipo_vehiculo_id' => $tipoMoto->tipo_vehiculo_id,
                'precio_base' => 3.00,
                'activo' => true,
                'configuracion' => json_encode(['incluye_interior' => true, 'incluye_exterior' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pulverizado',
                'descripcion' => 'Aplicación de pulverizado protector para motocicletas',
                'tipo_vehiculo_id' => $tipoMoto->tipo_vehiculo_id,
                'precio_base' => 2.00,
                'activo' => true,
                'configuracion' => json_encode(['tipo' => 'protector']),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Servicios para AUTO PEQUEÑO
            [
                'nombre' => 'Lavado Completo',
                'descripcion' => 'Lavado completo interior y exterior para autos pequeños',
                'tipo_vehiculo_id' => $tipoAutoPequeno->tipo_vehiculo_id,
                'precio_base' => 8.00,
                'activo' => true,
                'configuracion' => json_encode(['incluye_interior' => true, 'incluye_exterior' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Solo Exterior',
                'descripcion' => 'Lavado únicamente exterior para autos pequeños',
                'tipo_vehiculo_id' => $tipoAutoPequeno->tipo_vehiculo_id,
                'precio_base' => 4.50,
                'activo' => true,
                'configuracion' => json_encode(['incluye_interior' => false, 'incluye_exterior' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pulverizado',
                'descripcion' => 'Aplicación de pulverizado protector para autos pequeños',
                'tipo_vehiculo_id' => $tipoAutoPequeno->tipo_vehiculo_id,
                'precio_base' => 2.00,
                'activo' => true,
                'configuracion' => json_encode(['tipo' => 'protector']),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Servicios para AUTO MEDIANO
            [
                'nombre' => 'Lavado Completo',
                'descripcion' => 'Lavado completo interior y exterior para autos medianos',
                'tipo_vehiculo_id' => $tipoAutoMediano->tipo_vehiculo_id,
                'precio_base' => 10.00,
                'activo' => true,
                'configuracion' => json_encode(['incluye_interior' => true, 'incluye_exterior' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Solo Exterior',
                'descripcion' => 'Lavado únicamente exterior para autos medianos',
                'tipo_vehiculo_id' => $tipoAutoMediano->tipo_vehiculo_id,
                'precio_base' => 5.00,
                'activo' => true,
                'configuracion' => json_encode(['incluye_interior' => false, 'incluye_exterior' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pulverizado',
                'descripcion' => 'Aplicación de pulverizado protector para autos medianos',
                'tipo_vehiculo_id' => $tipoAutoMediano->tipo_vehiculo_id,
                'precio_base' => 2.00,
                'activo' => true,
                'configuracion' => json_encode(['tipo' => 'protector']),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Servicios para CAMIONETAS
            [
                'nombre' => 'Lavado Completo',
                'descripcion' => 'Lavado completo interior y exterior para camionetas',
                'tipo_vehiculo_id' => $tipoCamioneta->tipo_vehiculo_id,
                'precio_base' => 10.00,
                'activo' => true,
                'configuracion' => json_encode(['incluye_interior' => true, 'incluye_exterior' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Solo Exterior',
                'descripcion' => 'Lavado únicamente exterior para camionetas',
                'tipo_vehiculo_id' => $tipoCamioneta->tipo_vehiculo_id,
                'precio_base' => 7.50,
                'activo' => true,
                'configuracion' => json_encode(['incluye_interior' => false, 'incluye_exterior' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Pulverizado',
                'descripcion' => 'Aplicación de pulverizado protector para camionetas',
                'tipo_vehiculo_id' => $tipoCamioneta->tipo_vehiculo_id,
                'precio_base' => 2.00,
                'activo' => true,
                'configuracion' => json_encode(['tipo' => 'protector']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('servicios')->insert($servicios);
    }
}
