<?php
// Migración para la tabla pagos_proveedores
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_proveedores', function (Blueprint $table) {
            $table->id('id_pago_proveedor'); // Clave primaria personalizada
            $table->unsignedBigInteger('proveedor_id'); // Clave foránea
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha');
            $table->text('descripcion')->nullable();
            $table->foreign('proveedor_id')->references('proveedor_id')->on('proveedores')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
            
            // Índice para performance
            $table->index(['proveedor_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_proveedores');
    }
};
