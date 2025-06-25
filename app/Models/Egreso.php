<?php
// Modelo Eloquent para egresos
// Representa los egresos del sistema (pagos y gastos)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'egresos';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'egreso_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'fecha',         // Fecha del egreso
        'tipo',         // Tipo de egreso
        'referencia_id',// ID de referencia (puede ser pago o gasto)
        'monto',        // Monto del egreso
        'descripcion',  // Descripción
    ];

    // Relaciones dinámicas según el tipo
    public function pagoProveedor()
    {
        return $this->belongsTo(PagoProveedor::class, 'referencia_id', 'id_pago_proveedor')
            ->where('tipo', 'pago_proveedor');
    }

    public function gastoGeneral()
    {
        return $this->belongsTo(GastoGeneral::class, 'referencia_id', 'gasto_general_id')
            ->where('tipo', 'gasto_general');
    }

    // Método para obtener el objeto referenciado según el tipo
    public function getReferencia()
    {
        switch ($this->tipo) {
            case 'pago_proveedor':
                return $this->pagoProveedor;
            case 'gasto_general':
                return $this->gastoGeneral;
            default:
                return null;
        }
    }
}
