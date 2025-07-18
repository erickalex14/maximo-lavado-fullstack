<?php
// Modelo Eloquent para ingresos
// Representa los ingresos del sistema (ventas y lavados)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingreso extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'ingresos';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'ingreso_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'fecha',         // Fecha del ingreso
        'tipo',         // Tipo de ingreso: venta, lavado, producto_automotriz, producto_despensa, servicio
        'referencia_id',// ID de referencia (puede ser venta_id, lavado_id, etc.)
        'monto',        // Monto del ingreso
        'descripcion',  // Descripción
    ];

    // Casting de tipos
    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    // Constantes para tipos de ingreso
    const TIPO_VENTA = 'venta';
    const TIPO_LAVADO = 'lavado';
    const TIPO_PRODUCTO_AUTOMOTRIZ = 'producto_automotriz';
    const TIPO_PRODUCTO_DESPENSA = 'producto_despensa';
    const TIPO_SERVICIO = 'servicio';

    /**
     * Relación: Un ingreso puede estar relacionado con una venta (V2.0)
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'referencia_id', 'venta_id')
                    ->where('tipo', self::TIPO_VENTA);
    }

    /**
     * Relación: Un ingreso puede estar relacionado con un lavado
     */
    public function lavado()
    {
        return $this->belongsTo(Lavado::class, 'referencia_id', 'lavado_id')
                    ->where('tipo', self::TIPO_LAVADO);
    }

    /**
     * Scope para obtener ingresos por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para obtener ingresos del día
     */
    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para obtener ingresos por rango de fechas
     */
    public function scopePorRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    /**
     * Accessor para obtener el monto formateado
     */
    public function getMontoFormateadoAttribute()
    {
        return '$' . number_format($this->monto, 2);
    }

    /**
     * Método para obtener el objeto referenciado según el tipo
     */
    public function getReferencia()
    {
        switch ($this->tipo) {
            case self::TIPO_VENTA:
                return $this->venta;
            case self::TIPO_LAVADO:
                return $this->lavado;
            default:
                return null;
        }
    }
}
