<?php
// Modelo Eloquent para clientes
// Representa a los clientes del sistema de lavado de autos

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'clientes';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'cliente_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'nombre',     // Nombre del cliente
        'telefono',   // Teléfono
        'email',      // Email
        'direccion',  // Dirección
        'cedula',     // Cédula
    ];

    // Relación: Un cliente puede tener muchos vehículos
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'cliente_id', 'cliente_id');
    }

    // Relación: Un cliente puede tener muchas facturas
    public function facturas()
    {
        return $this->hasMany(Factura::class, 'cliente_id', 'cliente_id');
    }
}
