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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id('servicio_id');
            $table->string('nombre'); // Lavado Completo, Solo Exterior, Pulverizado
            $table->text('descripcion')->nullable();
            $table->foreignId('tipo_vehiculo_id')->constrained('tipos_vehiculos', 'tipo_vehiculo_id')->onDelete('cascade');
            $table->decimal('precio_base', 8, 2); // Precio por defecto por tipo de vehículo
            $table->boolean('activo')->default(true);
            $table->json('configuracion')->nullable(); // Configuraciones adicionales del servicio
            $table->timestamps();
            $table->softDeletes(); // Soft deletes incluido
            
            // Índices para performance
            $table->index(['tipo_vehiculo_id', 'activo']);
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
