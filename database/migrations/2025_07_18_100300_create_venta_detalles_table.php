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
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id('venta_detalle_id');
            $table->foreignId('venta_id')->constrained('ventas', 'venta_id')->onDelete('cascade');
            
            // Tipo de item: 'producto_automotriz', 'producto_despensa', 'servicio'
            $table->enum('tipo_item', ['producto_automotriz', 'producto_despensa', 'servicio']);
            
            // Referencias polimórficas a diferentes tipos de productos/servicios
            $table->unsignedBigInteger('item_id'); // ID del producto o servicio
            $table->string('item_nombre'); // Nombre del item (para historial)
            $table->text('item_descripcion')->nullable();
            
            // Campos para servicios (trazabilidad)
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos', 'vehiculo_id')->onDelete('set null');
            $table->foreignId('empleado_id')->nullable()->constrained('empleados', 'empleado_id')->onDelete('set null');
            
            // Campos de venta
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            
            $table->timestamps();
            $table->softDeletes(); // Soft deletes incluido
            
            // Índices para performance
            $table->index(['venta_id', 'tipo_item']);
            $table->index(['item_id', 'tipo_item']);
            $table->index('vehiculo_id');
            $table->index('empleado_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta_detalles');
    }
};
