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
        'cliente_id',        // ID del cliente propietario
        'tipo_vehiculo_id',  // FK al tipo de vehículo (nueva relación V2.0)
        'matricula',        // Matrícula (puede ser null para motos)
        'descripcion',      // Descripción adicional
    ];

    // No hay casting adicional, campos son string y IDs

    /**
     * Relación: Un vehículo pertenece a un cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    /**
     * Relación: Un vehículo pertenece a un tipo de vehículo (V2.0)
     */
    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_id', 'tipo_vehiculo_id');
    }

    /**
     * Relación: Un vehículo puede tener muchos lavados
     */
    public function lavados()
    {
        return $this->hasMany(Lavado::class, 'vehiculo_id', 'vehiculo_id');
    }

    /**
     * Relación: Un vehículo puede estar en muchas ventas (V2.0)
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'vehiculo_id', 'vehiculo_id');
    }

    /**
     * Scope para obtener vehículos por tipo
     */
    public function scopePorTipo($query, $tipoVehiculoId)
    {
        return $query->where('tipo_vehiculo_id', $tipoVehiculoId);
    }

    /**
     * Scope para obtener vehículos con matrícula
     */
    public function scopeConMatricula($query)
    {
        return $query->whereNotNull('matricula');
    }
}
