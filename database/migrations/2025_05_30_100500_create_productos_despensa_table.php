<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_despensa', function (Blueprint $table) {
            $table->id('producto_despensa_id');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio_venta', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_despensa');
    }
};
