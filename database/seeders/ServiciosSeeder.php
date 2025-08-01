<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;
use App\Models\TipoVehiculo;

class ServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primero obtener los tipos de vehículo (flexible con nombres)
        $tipos = TipoVehiculo::all();
        
        if ($tipos->isEmpty()) {
            $this->command->error('Error: No hay tipos de vehículo. Crea algunos desde los endpoints primero.');
            return;
        }

        $this->command->info('Tipos de vehículo encontrados:');
        foreach ($tipos as $tipo) {
            $this->command->info("- {$tipo->tipo_vehiculo_id}: {$tipo->nombre}");
        }

        // Buscar tipos específicos por nombre (case insensitive)
        $moto = $tipos->first(function($tipo) {
            return stripos($tipo->nombre, 'moto') !== false;
        });
        
        $autoPequeno = $tipos->first(function($tipo) {
            return stripos($tipo->nombre, 'pequeño') !== false || stripos($tipo->nombre, 'pequeno') !== false;
        });
        
        $autoMediano = $tipos->first(function($tipo) {
            return stripos($tipo->nombre, 'mediano') !== false;
        });
        
        $camioneta = $tipos->first(function($tipo) {
            return stripos($tipo->nombre, 'camioneta') !== false;
        });

        $serviciosCreados = 0;

        $serviciosCreados = 0;

        // Servicios para Motocicleta
        if ($moto) {
            Servicio::create([
                'nombre' => 'Lavado Completo',
                'descripcion' => 'Lavado completo interior y exterior para motocicleta',
                'tipo_vehiculo_id' => $moto->tipo_vehiculo_id,
                'precio_base' => 3.00,
                'activo' => true
            ]);
            $serviciosCreados++;
            $this->command->info("✅ Creado: Lavado Completo para {$moto->nombre}");
        }

        // Servicios para Auto Pequeño
        if ($autoPequeno) {
            Servicio::create([
                'nombre' => 'Lavado Completo',
                'descripcion' => 'Lavado completo interior y exterior para auto pequeño',
                'tipo_vehiculo_id' => $autoPequeno->tipo_vehiculo_id,
                'precio_base' => 8.00,
                'activo' => true
            ]);

            Servicio::create([
                'nombre' => 'Lavado Solo Exterior',
                'descripcion' => 'Lavado solo por fuera para auto pequeño',
                'tipo_vehiculo_id' => $autoPequeno->tipo_vehiculo_id,
                'precio_base' => 4.50,
                'activo' => true
            ]);
            $serviciosCreados += 2;
            $this->command->info("✅ Creados: 2 servicios para {$autoPequeno->nombre}");
        }

        // Servicios para Auto Mediano
        if ($autoMediano) {
            Servicio::create([
                'nombre' => 'Lavado Completo',
                'descripcion' => 'Lavado completo interior y exterior para auto mediano',
                'tipo_vehiculo_id' => $autoMediano->tipo_vehiculo_id,
                'precio_base' => 10.00,
                'activo' => true
            ]);

            Servicio::create([
                'nombre' => 'Lavado Solo Exterior',
                'descripcion' => 'Lavado solo por fuera para auto mediano',
                'tipo_vehiculo_id' => $autoMediano->tipo_vehiculo_id,
                'precio_base' => 5.00,
                'activo' => true
            ]);
            $serviciosCreados += 2;
            $this->command->info("✅ Creados: 2 servicios para {$autoMediano->nombre}");
        }

        // Servicios para Camioneta
        if ($camioneta) {
            Servicio::create([
                'nombre' => 'Lavado Completo',
                'descripcion' => 'Lavado completo interior y exterior para camioneta',
                'tipo_vehiculo_id' => $camioneta->tipo_vehiculo_id,
                'precio_base' => 10.00,
                'activo' => true
            ]);

            Servicio::create([
                'nombre' => 'Lavado Solo Exterior',
                'descripcion' => 'Lavado solo por fuera para camioneta',
                'tipo_vehiculo_id' => $camioneta->tipo_vehiculo_id,
                'precio_base' => 7.50,
                'activo' => true
            ]);
            $serviciosCreados += 2;
            $this->command->info("✅ Creados: 2 servicios para {$camioneta->nombre}");
        }

        $this->command->info("✅ Total: {$serviciosCreados} servicios creados exitosamente");
        $this->command->info('📋 Nota: El pulverizado (+$2) se añade dinámicamente en las ventas');
    }
}
