<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ventas_productos_automotrices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('total', 10, 2);
            $table->dateTime('fecha');
            $table->foreign('producto_id')->references('producto_automotriz_id')->on('productos_automotrices');
            $table->foreign('cliente_id')->references('cliente_id')->on('clientes');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ventas_productos_automotrices');
    }
};
