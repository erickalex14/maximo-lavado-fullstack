<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egresos', function (Blueprint $table) {
            $table->id('egreso_id');
            $table->date('fecha');
            $table->enum('tipo', ['salario', 'proveedor', 'gasto_general']);
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egresos');
    }
};
