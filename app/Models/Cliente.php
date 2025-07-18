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
        'nombre',       // Nombre del cliente
        'telefono',     // Teléfono
        'email',        // Email
        'direccion',    // Dirección
        'cedula',       // Cédula
    ];

    // No hay casting adicional según migración

    /**
     * Relación: Un cliente puede tener muchos vehículos
     */
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'cliente_id', 'cliente_id');
    }

    /**
     * Relación: Un cliente puede tener muchas ventas (V2.0)
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id', 'cliente_id');
    }

    /**
     * Relación: Un cliente puede tener muchas facturas electrónicas (V2.0)
     */
    public function facturasElectronicas()
    {
        return $this->hasManyThrough(
            FacturaElectronica::class,
            Venta::class,
            'cliente_id', // FK en ventas
            'venta_id',   // FK en facturas_electronicas
            'cliente_id', // PK en clientes
            'venta_id'    // PK en ventas
        );
    }

    /**
     * Scope para buscar clientes por nombre
     */
    public function scopePorNombre($query, $nombre)
    {
        return $query->where('nombre', 'like', '%' . $nombre . '%');
    }

    /**
     * Scope para buscar clientes por cédula
     */
    public function scopePorCedula($query, $cedula)
    {
        return $query->where('cedula', $cedula);
    }
}
