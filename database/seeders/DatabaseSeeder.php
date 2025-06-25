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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Cliente::factory(20)->create();
        Empleado::factory(10)->create();
        Proveedor::factory(8)->create();
        ProductoAutomotriz::factory(15)->create();
        ProductoDespensa::factory(15)->create();
        Vehiculo::factory(25)->create();
        Lavado::factory(30)->create();
        Factura::factory(20)->create();
        FacturaDetalle::factory(60)->create();
        // Actualizar total de cada factura
        foreach (Factura::all() as $factura) {
            $total = $factura->detalles()->sum('subtotal');
            $factura->total = $total;
            $factura->save();
        }
        Ingreso::factory(40)->create();
        Egreso::factory(40)->create();
        GastoGeneral::factory(20)->create();
    }
}
