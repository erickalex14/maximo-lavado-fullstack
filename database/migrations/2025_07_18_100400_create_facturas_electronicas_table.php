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
        Schema::create('facturas_electronicas', function (Blueprint $table) {
            $table->id('factura_electronica_id');
            $table->foreignId('venta_id')->constrained('ventas', 'venta_id')->onDelete('cascade');
            
            // Datos del emisor (empresa)
            $table->string('ruc_emisor', 13);
            $table->string('razon_social_emisor');
            $table->string('direccion_emisor');
            $table->string('establecimiento', 3)->default('001');
            $table->string('punto_emision', 3)->default('001');
            
            // Datos del comprador
            $table->string('identificacion_comprador', 13);
            $table->string('razon_social_comprador');
            $table->string('direccion_comprador')->nullable();
            $table->string('email_comprador')->nullable();
            
            // Información del documento electrónico
            $table->string('tipo_documento', 2)->default('01'); // 01: Factura
            $table->bigInteger('secuencial'); // Número secuencial
            $table->string('ambiente', 1)->default('1'); // 1: Pruebas, 2: Producción
            $table->string('tipo_emision', 1)->default('1'); // 1: Emisión normal
            
            // Clave de acceso y autenticación SRI
            $table->string('clave_acceso', 49)->unique()->nullable();
            $table->string('numero_autorizacion', 37)->nullable();
            $table->dateTime('fecha_autorizacion')->nullable();
            
            // Estado del proceso SRI
            $table->enum('estado_sri', ['GENERADA', 'ENVIADA', 'AUTORIZADA', 'RECHAZADA', 'ANULADA'])->default('GENERADA');
            
            // Valores monetarios (para reportes rápidos)
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            
            // XML y respuesta del SRI
            $table->longText('xml_documento')->nullable();
            $table->longText('xml_autorizado')->nullable();
            $table->text('mensaje_sri')->nullable();
            $table->json('errores_sri')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // Soft deletes incluido
            
            // Índices únicos y de performance
            $table->unique(['establecimiento', 'punto_emision', 'secuencial']);
            $table->index(['ruc_emisor', 'fecha_autorizacion']);
            $table->index('identificacion_comprador');
            $table->index(['estado_sri', 'fecha_autorizacion']);
            $table->index('clave_acceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_electronicas');
    }
};
