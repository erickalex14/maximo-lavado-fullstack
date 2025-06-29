<?php
// Modelo Eloquent para proveedores
// Representa los proveedores del sistema

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'proveedores';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'proveedor_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'nombre',           // Nombre del proveedor
        'email',            // Email
        'descripcion',      // Descripción
        'telefono',         // Teléfono
        'deuda_pendiente',  // Deuda pendiente
    ];

    public function pagos()
    {
        return $this->hasMany(PagoProveedor::class, 'proveedor_id', 'proveedor_id');
    }
}
