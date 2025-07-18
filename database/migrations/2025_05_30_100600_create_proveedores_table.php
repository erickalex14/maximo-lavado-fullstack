<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('proveedor_id');
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('telefono')->nullable();
            $table->decimal('deuda_pendiente', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
