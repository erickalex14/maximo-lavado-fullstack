<?php
// Modelo Eloquent para productos automotrices
// Representa los productos automotrices en el inventario

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoAutomotriz extends Model
{
    use HasFactory;

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

    // Indica si la clave primaria es autoincremental
    public $incrementing = true;

    // Tipo de la clave primaria
    protected $keyType = 'int';

    // Relación: Un producto automotriz puede estar en muchos detalles de factura
    public function detallesFactura()
    {
        // La clave foránea en FacturaDetalle es producto_id
        return $this->hasMany(FacturaDetalle::class, 'producto_id', 'producto_automotriz_id');
    }
}
