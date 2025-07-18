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
        Schema::create('tipos_vehiculos', function (Blueprint $table) {
            $table->id('tipo_vehiculo_id');
            $table->string('nombre'); // moto, auto_pequeno, auto_mediano, camioneta
            $table->string('descripcion')->nullable();
            $table->boolean('requiere_matricula')->default(true); // false para motos
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Soft deletes incluido
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_vehiculos');
    }
};
