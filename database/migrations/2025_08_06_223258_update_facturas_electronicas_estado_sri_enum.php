<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el ENUM para incluir 'BORRADOR' como estado inicial
        DB::statement("ALTER TABLE facturas_electronicas MODIFY COLUMN estado_sri ENUM('BORRADOR', 'GENERADA', 'ENVIADA', 'AUTORIZADA', 'RECHAZADA', 'ANULADA') DEFAULT 'BORRADOR'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al ENUM original
        DB::statement("ALTER TABLE facturas_electronicas MODIFY COLUMN estado_sri ENUM('GENERADA', 'ENVIADA', 'AUTORIZADA', 'RECHAZADA', 'ANULADA') DEFAULT 'GENERADA'");
    }
};
