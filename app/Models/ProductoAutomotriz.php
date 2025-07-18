<?php
// Modelo Eloquent para productos automotrices
// Representa los productos automotrices en el inventario

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoAutomotriz extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'productos_automotrices';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'producto_automotriz_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'codigo',        // Código único del producto
        'nombre',        // Nombre del producto
        'descripcion',   // Descripción del producto
        'precio_venta',  // Precio de venta
        'stock',         // Nivel de stock
        'activo',        // Estado del producto (activo/inactivo)
    ];

    // Casting de tipos
    protected $casts = [
        'precio_venta' => 'decimal:2',
        'stock' => 'integer',
        'activo' => 'boolean',
    ];

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Tipo de la clave primaria
    protected $keyType = 'int';

    /**
     * Relación: Un producto automotriz puede estar en muchos detalles de venta (V2.0)
     */
    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class, 'item_id', 'producto_automotriz_id')
                    ->where('tipo_item', 'producto');
    }

    /**
     * Scope para obtener solo productos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener productos con stock bajo
     */
    public function scopeStockBajo($query, $limite = 5)
    {
        return $query->where('stock', '<=', $limite);
    }

    /**
     * Accessor para obtener precio formateado
     */
    public function getPrecioVentaFormateadoAttribute()
    {
        return '$' . number_format($this->precio_venta, 2);
    }

    /**
     * Método para validar stock disponible
     */
    public function tieneStock($cantidad = 1)
    {
        return $this->stock >= $cantidad;
    }

    /**
     * Método para reducir stock
     */
    public function reducirStock($cantidad)
    {
        if ($this->tieneStock($cantidad)) {
            $this->stock -= $cantidad;
            $this->save();
            return true;
        }
        return false;
    }
}
