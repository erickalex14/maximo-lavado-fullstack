<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaProductoAutomotriz extends Model
{
    use HasFactory;
    protected $table = 'ventas_productos_automotrices';
    protected $fillable = [
        'producto_id', 'cliente_id', 'cantidad', 'precio_unitario', 'total', 'fecha'
    ];
    public function productoAutomotriz() { return $this->belongsTo(ProductoAutomotriz::class, 'producto_id', 'producto_automotriz_id'); }
    public function cliente() { return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id'); }
}
