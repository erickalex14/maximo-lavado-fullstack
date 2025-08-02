<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para detalles de venta
 * Permite vender tanto servicios como productos
 */
class VentaDetalle extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'venta_detalles';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'venta_detalle_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'venta_id',             // FK a la venta
        'tipo_item',            // servicio, producto_automotriz, producto_despensa
        'item_id',              // ID del servicio o producto
        'item_nombre',          // Nombre del servicio/producto (snapshot)
        'item_descripcion',     // Descripción del item (snapshot)
        'vehiculo_id',          // FK al vehículo (para servicios)
        'empleado_id',          // FK al empleado (para servicios)
        'cantidad',             // Cantidad vendida
        'precio_unitario',      // Precio por unidad al momento de la venta
        'subtotal',             // cantidad * precio_unitario
        'descuento',            // Descuento aplicado
        'total',                // subtotal - descuento
    ];

    // Casting de tipos
    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Constantes para tipos de item
    const TIPO_SERVICIO = 'servicio';
    const TIPO_PRODUCTO = 'producto';

    /**
     * Relación: Un detalle pertenece a una venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'venta_id');
    }

    /**
     * Relación polimórfica: Un detalle puede ser un servicio
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'item_id', 'servicio_id')
                    ->where('tipo_item', self::TIPO_SERVICIO);
    }

    /**
     * Relación polimórfica: Un detalle puede ser un producto automotriz
     */
    public function productoAutomotriz()
    {
        return $this->belongsTo(ProductoAutomotriz::class, 'item_id', 'producto_automotriz_id')
                    ->where('tipo_item', self::TIPO_PRODUCTO);
    }

    /**
     * Relación polimórfica: Un detalle puede ser un producto de despensa
     */
    public function productoDespensa()
    {
        return $this->belongsTo(ProductoDespensa::class, 'item_id', 'producto_despensa_id')
                    ->where('tipo_item', self::TIPO_PRODUCTO);
    }

    /**
     * Scope para obtener solo detalles de servicios
     */
    public function scopeServicios($query)
    {
        return $query->where('tipo_item', self::TIPO_SERVICIO);
    }

    /**
     * Scope para obtener solo detalles de productos
     */
    public function scopeProductos($query)
    {
        return $query->where('tipo_item', self::TIPO_PRODUCTO);
    }

    /**
     * Scope para obtener detalles por tipo de item
     */
    public function scopePorTipoItem($query, $tipo)
    {
        return $query->where('tipo_item', $tipo);
    }

    /**
     * Accessor para obtener el precio unitario formateado
     */
    public function getPrecioUnitarioFormateadoAttribute()
    {
        return '$' . number_format($this->precio_unitario, 2);
    }

    /**
     * Accessor para obtener el subtotal formateado
     */
    public function getSubtotalFormateadoAttribute()
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * Método para obtener el item relacionado (servicio o producto)
     */
    public function getItemAttribute()
    {
        switch ($this->tipo_item) {
            case self::TIPO_SERVICIO:
                return $this->servicio;
            case self::TIPO_PRODUCTO:
                // Intentar primero producto automotriz, luego despensa
                return $this->productoAutomotriz ?? $this->productoDespensa;
            default:
                return null;
        }
    }

    /**
     * Método para calcular subtotal automáticamente
     */
    public function calcularSubtotal()
    {
        $precioConDescuento = $this->precio_unitario - $this->descuento_unitario;
        $this->subtotal = $this->cantidad * $precioConDescuento;
        return $this->subtotal;
    }

    /**
     * Boot method para calcular subtotal automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detalle) {
            $detalle->calcularSubtotal();
        });
    }

    /**
     * Método para validar disponibilidad de stock (solo productos)
     */
    public function validarStock()
    {
        if ($this->tipo_item === self::TIPO_PRODUCTO) {
            $producto = $this->item;
            if ($producto && isset($producto->stock)) {
                return $producto->stock >= $this->cantidad;
            }
        }
        return true; // Servicios siempre disponibles
    }

    /**
     * Método para actualizar stock después de venta
     */
    public function actualizarStock()
    {
        if ($this->tipo_item === self::TIPO_PRODUCTO) {
            $producto = $this->item;
            if ($producto && isset($producto->stock)) {
                $producto->stock -= $this->cantidad;
                $producto->save();
            }
        }
    }
}
