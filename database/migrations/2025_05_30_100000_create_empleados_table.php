<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('empleado_id');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('telefono');
            $table->string('cedula')->unique();
            $table->enum('tipo_salario', ['mensual', 'diario', 'quincenal', 'semanal']);
            $table->decimal('salario', 10, 2);
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
