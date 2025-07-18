<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id('ingreso_id');
            $table->date('fecha');
            $table->enum('tipo', ['venta', 'lavado', 'producto_automotriz', 'producto_despensa', 'servicio']);
            $table->unsignedBigInteger('referencia_id')->nullable(); // ID de venta, lavado, etc.
            $table->decimal('monto', 10, 2);
            $table->string('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
            
            // Índices para performance
            $table->index(['fecha', 'tipo']);
            $table->index('referencia_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};
