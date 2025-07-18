<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lavados', function (Blueprint $table) {
            $table->id('lavado_id');
            $table->foreignId('vehiculo_id')->constrained('vehiculos', 'vehiculo_id')->onDelete('cascade');
            $table->foreignId('empleado_id')->constrained('empleados', 'empleado_id')->onDelete('cascade');
            $table->foreignId('servicio_id')->nullable()->constrained('servicios', 'servicio_id')->onDelete('set null');
            $table->date('fecha');
            $table->enum('tipo_lavado', ['completo', 'solo_fuera', 'solo_por_dentro']);
            $table->decimal('precio', 10, 2);
            $table->boolean('pulverizado')->default(false);
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
            
            // Índices para performance
            $table->index(['fecha', 'empleado_id']);
            $table->index(['vehiculo_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lavados');
    }
};
