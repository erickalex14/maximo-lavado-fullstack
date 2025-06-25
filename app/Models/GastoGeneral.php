<?php
// Modelo Eloquent para gastos generales
// Representa los gastos generales del sistema

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoGeneral extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'gastos_generales';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'gasto_general_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'nombre',        // Nombre del gasto
        'descripcion',   // Descripción
        'monto',         // Monto del gasto
        'fecha',         // Fecha del gasto
    ];
}
