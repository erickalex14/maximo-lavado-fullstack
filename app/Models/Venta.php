<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para ventas unificadas
 * Maneja tanto servicios como productos
 */
class Venta extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'ventas';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'venta_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'cliente_id',           // FK al cliente
        'empleado_id',          // FK al empleado que realiza la venta
        'vehiculo_id',          // FK al vehículo (si es servicio de lavado)
        'tipo_venta',           // servicio, producto, mixto
        'subtotal',             // Suma de todos los detalles
        'descuento',            // Descuento aplicado
        'impuesto',             // IVA calculado
        'total',                // Subtotal - descuento + impuesto
        'estado',               // pendiente, procesando, completada, cancelada
        'fecha_venta',          // Fecha de la venta
        'observaciones',        // Notas adicionales
        'metadatos',            // JSON con datos adicionales
    ];

    // Casting de tipos
    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_venta' => 'datetime',
        'metadatos' => 'array',
    ];

    // Constantes para tipos de venta
    const TIPO_SERVICIO = 'servicio';
    const TIPO_PRODUCTO = 'producto';
    const TIPO_MIXTO = 'mixto';

    // Constantes para estados
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PROCESANDO = 'procesando';
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_CANCELADA = 'cancelada';

    /**
     * Relación: Una venta pertenece a un cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    /**
     * Relación: Una venta es atendida por un empleado
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'empleado_id');
    }

    /**
     * Relación: Una venta puede estar asociada a un vehículo (para servicios)
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'vehiculo_id');
    }

    /**
     * Relación: Una venta tiene muchos detalles
     */
    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id', 'venta_id');
    }

    /**
     * Relación: Una venta genera una factura electrónica
     */
    public function facturaElectronica()
    {
        return $this->hasOne(FacturaElectronica::class, 'venta_id', 'venta_id');
    }

    /**
     * Relación: Una venta genera un ingreso
     */
    public function ingreso()
    {
        return $this->hasOne(Ingreso::class, 'venta_id', 'venta_id');
    }

    /**
     * Relación: Una venta puede tener un lavado asociado (trazabilidad)
     */
    public function lavado()
    {
        return $this->hasOne(Lavado::class, 'venta_id', 'venta_id');
    }

    /**
     * Scope para obtener ventas por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_venta', $tipo);
    }

    /**
     * Scope para obtener ventas por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para obtener ventas del día
     */
    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        return $query->whereDate('fecha_venta', $fecha);
    }

    /**
     * Scope para obtener ventas por rango de fechas
     */
    public function scopePorRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_venta', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope para obtener ventas completadas
     */
    public function scopeCompletadas($query)
    {
        return $query->where('estado', self::ESTADO_COMPLETADA);
    }

    /**
     * Accessor para obtener el total formateado
     */
    public function getTotalFormateadoAttribute()
    {
        return '$' . number_format($this->total, 2);
    }

    /**
     * Accessor para obtener el subtotal formateado
     */
    public function getSubtotalFormateadoAttribute()
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * Método para calcular totales desde los detalles
     */
    public function calcularTotales()
    {
        $this->subtotal = $this->detalles->sum('subtotal');
        $this->impuesto = $this->subtotal * 0.12; // IVA Ecuador 12%
        $this->total = $this->subtotal - $this->descuento + $this->impuesto;
        $this->save();
    }

    /**
     * Método para marcar como completada
     */
    public function marcarCompletada()
    {
        $this->estado = self::ESTADO_COMPLETADA;
        $this->save();
    }

    /**
     * Método para cancelar venta
     */
    public function cancelar($razon = null)
    {
        $this->estado = self::ESTADO_CANCELADA;
        $this->observaciones = $razon ? "Cancelada: {$razon}" : 'Cancelada';
        $this->save();
    }
}
