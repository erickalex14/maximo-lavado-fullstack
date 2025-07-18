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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('venta_id');
            $table->string('numero_venta')->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes', 'cliente_id')->onDelete('set null');
            $table->date('fecha');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('estado', ['pendiente', 'completada', 'cancelada'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users', 'id')->onDelete('set null'); // Usuario que registra la venta
            $table->timestamps();
            $table->softDeletes(); // Soft deletes incluido
            
            // Índices para performance
            $table->index(['fecha', 'estado']);
            $table->index('cliente_id');
            $table->index('numero_venta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
