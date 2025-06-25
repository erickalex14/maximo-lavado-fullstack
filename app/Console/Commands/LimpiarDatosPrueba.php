<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Vehiculo;
use App\Models\Lavado;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Empleado;
use App\Models\Proveedor;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use App\Models\Ingreso;
use App\Models\Egreso;
use App\Models\GastoGeneral;
use App\Models\PagoProveedor;

class LimpiarDatosPrueba extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datos:limpiar {--confirmar : Confirmar la eliminación de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia todos los datos de prueba del sistema para empezar con datos reales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('confirmar')) {
            $this->error('⚠️  Este comando eliminará TODOS los datos del sistema.');
            $this->info('Para confirmar, ejecuta: php artisan datos:limpiar --confirmar');
            return;
        }

        if (!$this->confirm('¿Estás seguro de que quieres eliminar TODOS los datos? Esta acción no se puede deshacer.')) {
            $this->info('Operación cancelada.');
            return;
        }

        $this->info('🧹 Iniciando limpieza de datos...');

        // Deshabilitar comprobaciones de clave foránea temporalmente
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tablas = [
            ['modelo' => Lavado::class, 'nombre' => 'Lavados'],
            ['modelo' => FacturaDetalle::class, 'nombre' => 'Detalles de Facturas'],
            ['modelo' => Factura::class, 'nombre' => 'Facturas'],
            ['modelo' => VentaProductoAutomotriz::class, 'nombre' => 'Ventas de Productos Automotrices'],
            ['modelo' => VentaProductoDespensa::class, 'nombre' => 'Ventas de Productos de Despensa'],
            ['modelo' => Vehiculo::class, 'nombre' => 'Vehículos'],
            ['modelo' => Cliente::class, 'nombre' => 'Clientes'],
            ['modelo' => ProductoAutomotriz::class, 'nombre' => 'Productos Automotrices'],
            ['modelo' => ProductoDespensa::class, 'nombre' => 'Productos de Despensa'],
            ['modelo' => PagoProveedor::class, 'nombre' => 'Pagos a Proveedores'],
            ['modelo' => Proveedor::class, 'nombre' => 'Proveedores'],
            ['modelo' => Empleado::class, 'nombre' => 'Empleados'],
            ['modelo' => GastoGeneral::class, 'nombre' => 'Gastos Generales'],
            ['modelo' => Egreso::class, 'nombre' => 'Egresos'],
            ['modelo' => Ingreso::class, 'nombre' => 'Ingresos'],
        ];

        foreach ($tablas as $tabla) {
            try {
                $count = $tabla['modelo']::count();
                if ($count > 0) {
                    $tabla['modelo']::truncate();
                    $this->info("✅ {$tabla['nombre']}: {$count} registros eliminados");
                } else {
                    $this->line("ℹ️  {$tabla['nombre']}: Ya estaba vacía");
                }
            } catch (\Exception $e) {
                $this->error("❌ Error al limpiar {$tabla['nombre']}: " . $e->getMessage());
            }
        }

        // Reactivar comprobaciones de clave foránea
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('✨ ¡Limpieza completada! El sistema está listo para datos reales.');
        $this->info('');
        $this->info('Puedes empezar agregando:');
        $this->info('1. Clientes desde /clientes');
        $this->info('2. Vehículos desde /vehiculos');  
        $this->info('3. Empleados desde /empleados');
        $this->info('4. Proveedores desde /proveedores');
    }
}
