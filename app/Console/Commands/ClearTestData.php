<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-test-data {--confirm : Confirmar la operación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar todos los datos de prueba de la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('confirm')) {
            $this->error('Esta operación eliminará TODOS los datos de la base de datos.');
            $this->info('Para confirmar, ejecuta: php artisan db:clear-test-data --confirm');
            return;
        }

        $this->info('Limpiando datos de prueba...');

        try {
            // Deshabilitar verificación de llaves foráneas
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Limpiar todas las tablas principales
            $this->info('Limpiando proveedores...');
            \App\Models\PagoProveedor::truncate();
            \App\Models\Proveedor::truncate();

            $this->info('Limpiando clientes y vehículos...');
            \App\Models\Vehiculo::truncate();
            \App\Models\Cliente::truncate();

            $this->info('Limpiando empleados...');
            \App\Models\Empleado::truncate();

            $this->info('Limpiando lavados...');
            \App\Models\Lavado::truncate();

            $this->info('Limpiando productos...');
            \App\Models\VentaProductoAutomotriz::truncate();
            \App\Models\VentaProductoDespensa::truncate();
            \App\Models\ProductoAutomotriz::truncate();
            \App\Models\ProductoDespensa::truncate();

            $this->info('Limpiando facturas...');
            \App\Models\FacturaDetalle::truncate();
            \App\Models\Factura::truncate();

            $this->info('Limpiando ingresos y egresos...');
            \App\Models\Ingreso::truncate();
            \App\Models\Egreso::truncate();
            \App\Models\GastoGeneral::truncate();

            // Rehabilitar verificación de llaves foráneas
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('✅ Datos de prueba eliminados exitosamente.');
            $this->info('La base de datos está ahora limpia y lista para datos reales.');

        } catch (\Exception $e) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error al limpiar datos: ' . $e->getMessage());
        }
    }
}
