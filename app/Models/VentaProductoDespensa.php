<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaProductoDespensa extends Model
{
    use HasFactory;
    protected $table = 'ventas_productos_despensa';
    protected $fillable = [
        'producto_id', 'cliente_id', 'cantidad', 'precio_unitario', 'total', 'fecha'
    ];
    public function productoDespensa() { return $this->belongsTo(ProductoDespensa::class, 'producto_id', 'producto_despensa_id'); }
    public function cliente() { return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id'); }
}
