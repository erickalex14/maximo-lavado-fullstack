<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('cliente_id');
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('direccion')->nullable();
            $table->string('cedula')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
