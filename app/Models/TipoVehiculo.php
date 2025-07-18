<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para tipos de vehículos dinámicos
 * Permite agregar nuevos tipos sin modificar código
 */
class TipoVehiculo extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'tipos_vehiculos';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'tipo_vehiculo_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'nombre',              // moto, auto_pequeno, auto_mediano, camioneta
        'descripcion',         // Descripción del tipo
        'requiere_matricula',  // Si requiere matrícula (false para motos)
        'activo',             // Estado del tipo
    ];

    // Casting de tipos
    protected $casts = [
        'requiere_matricula' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Relación: Un tipo de vehículo puede tener muchos vehículos
     */
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'tipo_vehiculo_id', 'tipo_vehiculo_id');
    }

    /**
     * Relación: Un tipo de vehículo puede tener muchos servicios
     */
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'tipo_vehiculo_id', 'tipo_vehiculo_id');
    }

    /**
     * Scope para obtener solo tipos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener tipos que requieren matrícula
     */
    public function scopeRequiereMatricula($query)
    {
        return $query->where('requiere_matricula', true);
    }
}
