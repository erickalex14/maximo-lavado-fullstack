<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_detalles', function (Blueprint $table) {
            $table->id('factura_detalle_id');
            $table->foreignId('factura_id')->constrained('facturas', 'factura_id')->onDelete('cascade');
            $table->unsignedBigInteger('lavado_id')->nullable();
            $table->unsignedBigInteger('venta_producto_automotriz_id')->nullable();
            $table->unsignedBigInteger('venta_producto_despensa_id')->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_detalles');
    }
};
