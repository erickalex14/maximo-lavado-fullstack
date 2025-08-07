<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo para facturas electrónicas
 * Cumple con requerimientos SRI Ecuador
 */
class FacturaElectronica extends Model
{
    use HasFactory, SoftDeletes;

    // Nombre de la tabla asociada
    protected $table = 'facturas_electronicas';

    // Nombre de la clave primaria personalizada
    protected $primaryKey = 'factura_electronica_id';

    // Permite asignación masiva en estos campos
    protected $fillable = [
        'venta_id',                 // FK a la venta
        'ruc_emisor',              // RUC del emisor
        'razon_social_emisor',      // Razón social del emisor
        'direccion_emisor',         // Dirección del emisor
        'establecimiento',          // Establecimiento (001)
        'punto_emision',           // Punto de emisión (001)
        'identificacion_comprador', // Cédula/RUC del comprador
        'razon_social_comprador',   // Razón social del comprador
        'direccion_comprador',      // Dirección del comprador
        'email_comprador',          // Email del comprador
        'tipo_documento',           // Tipo documento (01: Factura)
        'secuencial',              // Número secuencial
        'ambiente',                // 1: Pruebas, 2: Producción
        'tipo_emision',            // 1: Normal, 2: Contingencia
        'xml_documento',           // XML del documento
        'xml_autorizado',          // XML autorizado por SRI
        'mensaje_sri',             // Mensaje del SRI
        'errores_sri',             // Errores del SRI (JSON)
        // ⚡ CAMPOS CRÍTICOS PARA SRI ⚡
        'estado_sri',              // Estado del procesamiento SRI
        'clave_acceso',            // Clave de acceso SRI
        'numero_autorizacion',     // Número de autorización SRI
        'fecha_autorizacion',      // Fecha de autorización SRI
        // Campos monetarios
        'subtotal',                // Subtotal de la factura
        'descuento',               // Descuento aplicado
        'iva',                     // IVA calculado
        'total',                   // Total de la factura
    ];

    // Casting de tipos
    protected $casts = [
        'secuencial' => 'integer',
        'errores_sri' => 'array',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Constantes para ambiente SRI
    const AMBIENTE_PRUEBAS = '1';
    const AMBIENTE_PRODUCCION = '2';

    // Constantes para tipo de emisión
    const EMISION_NORMAL = '1';
    const EMISION_CONTINGENCIA = '2';

    // Constantes para tipo de documento
    const TIPO_FACTURA = '01';

    /**
     * Relación: Una factura electrónica pertenece a una venta
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'venta_id');
    }

    /**
     * Relación: Una factura electrónica accede al cliente a través de la venta
     */
    public function cliente()
    {
        return $this->hasOneThrough(
            Cliente::class,
            Venta::class,
            'venta_id',     // FK en ventas
            'cliente_id',   // FK en clientes  
            'venta_id',     // PK en facturas_electronicas
            'cliente_id'    // PK en ventas
        );
    }

    /**
     * Scope para obtener facturas por ambiente
     */
    public function scopePorAmbiente($query, $ambiente)
    {
        return $query->where('ambiente', $ambiente);
    }

    /**
     * Scope para obtener facturas del día
     */
    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        return $query->whereDate('created_at', $fecha);
    }

    /**
     * Método para generar número secuencial
     */
    public static function generarSecuencial()
    {
        $ultimaFactura = self::orderBy('secuencial', 'desc')->first();
        return $ultimaFactura ? $ultimaFactura->secuencial + 1 : 1;
    }

    /**
     * Método para verificar si tiene errores SRI
     */
    public function tieneErrores()
    {
        return !empty($this->errores_sri);
    }
}
