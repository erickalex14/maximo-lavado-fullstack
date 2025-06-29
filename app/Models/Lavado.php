<?php
// Modelo Eloquent para lavados
// Representa los servicios de lavado realizados a los vehículos

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lavado extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'lavados';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'lavado_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'vehiculo_id',   // ID del vehículo
        'empleado_id',   // ID del empleado que realizó el lavado
        'fecha',         // Fecha del lavado
        'tipo_lavado',   // Tipo de lavado
        'precio',        // Precio del lavado
        'pulverizado',   // Si se aplicó pulverizado
    ];

    // Relación: Un lavado pertenece a un vehículo
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'vehiculo_id');
    }

    // Relación: Un lavado pertenece a un empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'empleado_id');
    }

    // Relación: Un lavado puede estar en muchos detalles de factura
    public function detallesFactura()
    {
        // La clave foránea en FacturaDetalle es lavado_id
        return $this->hasMany(FacturaDetalle::class, 'lavado_id', 'lavado_id');
    }
}
