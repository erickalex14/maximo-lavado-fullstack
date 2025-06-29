<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tablas principales del sistema
        $tables = [
            'lavados',
            'ventas_productos_automotrices',
            'ventas_productos_despensa',
            'facturas',
            'factura_detalles',
            'ingresos',
            'egresos',
            'clientes',
            'vehiculos',
            'empleados',
            'productos_automotrices',
            'productos_despensa',
            'proveedores',
            'pagos_proveedores',
            'gastos_generales',
            'users'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tablas principales del sistema
        $tables = [
            'lavados',
            'ventas_productos_automotrices',
            'ventas_productos_despensa',
            'facturas',
            'factura_detalles',
            'ingresos',
            'egresos',
            'clientes',
            'vehiculos',
            'empleados',
            'productos_automotrices',
            'productos_despensa',
            'proveedores',
            'pagos_proveedores',
            'gastos_generales',
            'users'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
