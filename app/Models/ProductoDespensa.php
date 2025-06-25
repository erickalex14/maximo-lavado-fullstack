<?php
// Modelo Eloquent para productos de despensa
// Representa los productos de despensa disponibles en el inventario

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoDespensa extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'productos_despensa';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'producto_despensa_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'nombre',        // Nombre del producto
        'descripcion',   // Descripción del producto
        'precio_venta',  // Precio de venta
        'stock',         // Nivel de stock
        'activo',        // Estado del producto (activo/inactivo)
    ];

    // Relación: Un producto de despensa puede estar en muchos detalles de factura
    public function detallesFactura()
    {
        // La clave foránea en FacturaDetalle es producto_id
        return $this->hasMany(FacturaDetalle::class, 'producto_id', 'producto_despensa_id');
    }
}
