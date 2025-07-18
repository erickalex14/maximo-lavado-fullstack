<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('vehiculo_id');
            $table->foreignId('cliente_id')->constrained('clientes', 'cliente_id')->onDelete('cascade');
            $table->foreignId('tipo_vehiculo_id')->constrained('tipos_vehiculos', 'tipo_vehiculo_id')->onDelete('cascade');
            $table->string('matricula')->nullable(); // Nullable - validación por tipo en modelo
            $table->string('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
