<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id('factura_id');
            $table->string('numero_factura')->unique();
            $table->foreignId('cliente_id')->constrained('clientes', 'cliente_id')->onDelete('cascade');
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
