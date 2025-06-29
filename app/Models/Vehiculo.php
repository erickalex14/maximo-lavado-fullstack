<?php
// Modelo Eloquent para vehículos
// Representa los vehículos registrados por los clientes

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'vehiculos';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'vehiculo_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'cliente_id',   // ID del cliente propietario
        'tipo',        // Tipo de vehículo
        'matricula',   // Matrícula (puede ser null para motos)
        'descripcion', // Descripción
    ];

    // Relación: Un vehículo pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    // Relación: Un vehículo puede tener muchos lavados
    public function lavados()
    {
        return $this->hasMany(Lavado::class, 'vehiculo_id', 'vehiculo_id');
    }
}
