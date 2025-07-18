<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos_generales', function (Blueprint $table) {
            $table->id('gasto_general_id');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
            
            // Índice para performance
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos_generales');
    }
};
