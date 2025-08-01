<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Proveedor;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\Lavado;
use App\Models\Vehiculo;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Ingreso;
use App\Models\Egreso;
use App\Models\GastoGeneral;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 👤 Usuarios del sistema
            UserSeeder::class,
            
            // 🚗 Datos maestros
            TiposVehiculosSeeder::class,
            ServiciosSeeder::class,
            
            // 📊 Datos de demostración
            DemoDataSeeder::class,
        ]);
    }
}
