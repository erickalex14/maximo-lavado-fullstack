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
            
            // Campos para facturación electrónica SRI Ecuador
            $table->string('numero_autorizacion', 37)->nullable();
            $table->string('clave_acceso', 49)->nullable();
            $table->timestamp('fecha_autorizacion')->nullable();
            $table->enum('estado_sri', ['BORRADOR', 'AUTORIZADA', 'RECHAZADA'])->default('BORRADOR');
            $table->string('tipo_identificacion_comprador', 2)->default('05'); // 04=RUC, 05=CEDULA, 06=PASAPORTE
            
            // Campos monetarios para SRI
            $table->decimal('subtotal_sin_impuestos', 12, 2);
            $table->decimal('subtotal_0', 12, 2)->default(0);
            $table->decimal('subtotal_12', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            
            $table->timestamps();
            $table->softDeletes(); // Soft deletes optimizado
            
            // Índices para performance
            $table->index(['fecha', 'estado_sri']);
            $table->index('clave_acceso');
            $table->index('numero_autorizacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
