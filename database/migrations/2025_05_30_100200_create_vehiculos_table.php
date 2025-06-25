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
            $table->enum('tipo', ['moto', 'camioneta', 'auto_pequeno', 'auto_mediano']);
            $table->string('matricula')->nullable();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
