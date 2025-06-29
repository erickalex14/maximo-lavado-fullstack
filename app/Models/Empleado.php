<?php
// Modelo Eloquent para empleados
// Representa a los empleados del sistema de lavado de autos

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'empleados';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'empleado_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'nombres',      // Nombres del empleado
        'apellidos',    // Apellidos del empleado
        'telefono',     // Teléfono
        'cedula',       // Cédula
        'tipo_salario', // Tipo de salario
        'salario',      // Monto del salario
    ];

    // Relación: Un empleado puede tener muchos lavados
    public function lavados()
    {
        return $this->hasMany(Lavado::class, 'empleado_id', 'empleado_id');
    }
}
