<?php
// Modelo Eloquent para pagos a proveedores
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagoProveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagos_proveedores';
    protected $primaryKey = 'id_pago_proveedor';
    protected $fillable = [
        'proveedor_id',
        'monto',
        'fecha',
        'descripcion',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'proveedor_id');
    }
}
