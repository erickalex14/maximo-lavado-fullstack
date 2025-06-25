<?php
// Modelo Eloquent para facturas
// Representa las facturas generadas para clientes

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'facturas';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'factura_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'numero_factura', // Número único de factura
        'cliente_id',    // ID del cliente
        'fecha',         // Fecha de la factura
        'descripcion',   // Descripción
        'total',         // Total de la factura
    ];

    // Relación: Una factura pertenece a un cliente
    public function cliente()
    {
        // La clave foránea es cliente_id y la clave primaria del modelo Cliente es cliente_id
        return $this->belongsTo(Cliente::class, 'cliente_id', 'cliente_id');
    }

    // Relación: Una factura tiene muchos detalles
    public function detalles()
    {
        // La clave foránea en FacturaDetalle es factura_id y la clave primaria de este modelo es factura_id
        return $this->hasMany(FacturaDetalle::class, 'factura_id', 'factura_id');
    }
}
