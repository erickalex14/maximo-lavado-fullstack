<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para servicios (ex-lavados)
 * Catálogo de servicios disponibles por tipo de vehículo
 */
class Servicio extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'servicios';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'servicio_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'nombre',               // Lavado Completo, Solo Exterior, Pulverizado
        'descripcion',          // Descripción del servicio
        'tipo_vehiculo_id',     // FK al tipo de vehículo
        'precio_base',          // Precio por defecto
        'activo',              // Estado del servicio
        'configuracion',       // JSON con configuraciones adicionales
    ];

    // Casting de tipos
    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
        'configuracion' => 'array',
    ];

    /**
     * Relación: Un servicio pertenece a un tipo de vehículo
     */
    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_id', 'tipo_vehiculo_id');
    }

    /**
     * Relación: Un servicio puede estar en muchos detalles de venta
     */
    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class, 'item_id', 'servicio_id')
                    ->where('tipo_item', 'servicio');
    }

    /**
     * Relación: Un servicio puede tener muchos lavados (trazabilidad histórica)
     */
    public function lavados()
    {
        return $this->hasMany(Lavado::class, 'servicio_id', 'servicio_id');
    }

    /**
     * Scope para obtener solo servicios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener servicios por tipo de vehículo
     */
    public function scopePorTipoVehiculo($query, $tipoVehiculoId)
    {
        return $query->where('tipo_vehiculo_id', $tipoVehiculoId);
    }

    /**
     * Scope para obtener servicios con precio en rango
     */
    public function scopePorRangoPrecio($query, $precioMin, $precioMax)
    {
        return $query->whereBetween('precio_base', [$precioMin, $precioMax]);
    }

    /**
     * Accessor para obtener el precio formateado
     */
    public function getPrecioFormateadoAttribute()
    {
        return '$' . number_format($this->precio_base, 2);
    }
}
