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
        'servicio_id',   // FK al servicio realizado (V2.0)
        'fecha',         // Fecha del lavado
        'tipo_lavado',   // Tipo de lavado: completo, solo_fuera, solo_por_dentro
        'precio',        // Precio del lavado
        'pulverizado',   // Si se aplicó pulverizado
    ];

    // Casting de tipos
    protected $casts = [
        'fecha' => 'date',
        'precio' => 'decimal:2',
        'pulverizado' => 'boolean',
    ];

    /**
     * Relación: Un lavado pertenece a un vehículo
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'vehiculo_id');
    }

    /**
     * Relación: Un lavado pertenece a un empleado
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'empleado_id');
    }

    /**
     * Relación: Un lavado pertenece a un servicio (V2.0)
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id', 'servicio_id');
    }

    /**
     * Scope para obtener lavados del día
     */
    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para obtener lavados por tipo
     */
    public function scopePorTipo($query, $tipoLavado)
    {
        return $query->where('tipo_lavado', $tipoLavado);
    }

    /**
     * Scope para obtener lavados con pulverizado
     */
    public function scopeConPulverizado($query)
    {
        return $query->where('pulverizado', true);
    }
}
