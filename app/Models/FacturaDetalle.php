<?php
// Modelo Eloquent para detalles de factura
// Representa los productos y servicios incluidos en una factura

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'factura_detalles';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'factura_detalle_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'factura_id',        // ID de la factura
        'lavado_id',        // ID del lavado (nullable)
        'cantidad',         // Cantidad
        'precio_unitario',  // Precio unitario
        'subtotal',         // Subtotal
        'venta_producto_automotriz_id', // ID de la venta de producto automotriz (nullable)
        'venta_producto_despensa_id', // ID de la venta de producto despensa (nullable)
    ];

    // Relación: Un detalle pertenece a una factura
    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id', 'factura_id');
    }

    // Relación: Un detalle puede pertenecer a un lavado
    public function lavado()
    {
        // La clave foránea es lavado_id y la clave primaria del modelo Lavado es lavado_id
        return $this->belongsTo(Lavado::class, 'lavado_id', 'lavado_id');
    }

    // Relación: Un detalle puede pertenecer a una venta de producto automotriz
    public function ventaProductoAutomotriz()
    {
        return $this->belongsTo(VentaProductoAutomotriz::class, 'venta_producto_automotriz_id', 'id');
    }

    // Relación: Un detalle puede pertenecer a una venta de producto despensa
    public function ventaProductoDespensa()
    {
        return $this->belongsTo(VentaProductoDespensa::class, 'venta_producto_despensa_id', 'id');
    }
}
