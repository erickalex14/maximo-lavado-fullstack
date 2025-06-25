<?php
// Modelo Eloquent para ingresos
// Representa los ingresos del sistema (ventas y lavados)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'ingresos';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'ingreso_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'fecha',         // Fecha del ingreso
        'tipo',         // Tipo de ingreso
        'referencia_id',// ID de referencia (puede ser lavado o venta)
        'monto',        // Monto del ingreso
        'descripcion',  // Descripción
    ];

    // Relaciones dinámicas según el tipo
    public function lavado()
    {
        return $this->belongsTo(Lavado::class, 'referencia_id', 'lavado_id')
            ->where('tipo', 'lavado');
    }

    public function ventaProductoAutomotriz()
    {
        return $this->belongsTo(VentaProductoAutomotriz::class, 'referencia_id', 'id')
            ->where('tipo', 'producto_automotriz');
    }

    public function ventaProductoDespensa()
    {
        return $this->belongsTo(VentaProductoDespensa::class, 'referencia_id', 'id')
            ->where('tipo', 'producto_despensa');
    }

    // Método para obtener el objeto referenciado según el tipo
    public function getReferencia()
    {
        switch ($this->tipo) {
            case 'lavado':
                return $this->lavado;
            case 'producto_automotriz':
                return $this->ventaProductoAutomotriz;
            case 'producto_despensa':
                return $this->ventaProductoDespensa;
            default:
                return null;
        }
    }
}
