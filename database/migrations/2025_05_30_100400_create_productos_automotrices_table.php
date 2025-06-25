<?php

// Migración para la tabla productos_automotrices
// Esta tabla almacena los productos automotrices disponibles en el inventario

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Ejecuta la migración: crea la tabla productos_automotrices
    public function up(): void
    {
        Schema::create('productos_automotrices', function (Blueprint $table) {
            // Clave primaria personalizada
            $table->id('producto_automotriz_id');
            // Código único del producto
            $table->string('codigo')->unique();
            // Nombre del producto
            $table->string('nombre');
            // Descripción opcional del producto
            $table->text('descripcion')->nullable();
            // Precio de venta
            $table->decimal('precio_venta', 10, 2);
            // Nivel de stock
            $table->integer('stock')->default(0);
            // Estado del producto (activo/inactivo)
            $table->boolean('activo')->default(true);
            // Timestamps de creación y actualización
            $table->timestamps();
        });
    }

    // Revierte la migración: elimina la tabla productos_automotrices
    public function down(): void
    {
        Schema::dropIfExists('productos_automotrices');
    }
};
