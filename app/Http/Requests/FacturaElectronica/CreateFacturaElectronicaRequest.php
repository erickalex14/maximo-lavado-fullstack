<?php

namespace App\Http\Requests\FacturaElectronica;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 🧾 CreateFacturaElectronicaRequest - Request para crear facturas electrónicas SRI
 * 
 * Validaciones consistentes con la migración facturas_electronicas
 */
class CreateFacturaElectronicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Venta asociada
            'venta_id' => 'required|integer|exists:ventas,venta_id',
            
            // Datos del emisor (empresa) - estos normalmente vienen de configuración
            'ruc_emisor' => 'nullable|string|size:13',
            'razon_social_emisor' => 'nullable|string|max:255',
            'direccion_emisor' => 'nullable|string|max:255',
            'establecimiento' => 'nullable|string|size:3',
            'punto_emision' => 'nullable|string|size:3',
            
            // Datos del comprador - normalmente vienen del cliente de la venta
            'identificacion_comprador' => 'nullable|string|max:13',
            'razon_social_comprador' => 'nullable|string|max:255',
            'direccion_comprador' => 'nullable|string|max:255',
            'email_comprador' => 'nullable|email|max:255',
            
            // Información del documento electrónico
            'tipo_documento' => 'nullable|string|size:2|in:01,04,05,06,07', // 01: Factura, 04: Nota crédito, etc.
            'ambiente' => 'nullable|string|size:1|in:1,2', // 1: Pruebas, 2: Producción
            'tipo_emision' => 'nullable|string|size:1|in:1,2', // 1: Normal, 2: Contingencia
            
            // Valores monetarios (normalmente calculados desde la venta)
            'subtotal' => 'nullable|numeric|min:0|max:999999999.99',
            'descuento' => 'nullable|numeric|min:0|max:999999999.99',
            'iva' => 'nullable|numeric|min:0|max:999999999.99',
            'total' => 'nullable|numeric|min:0|max:999999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'venta_id.required' => 'La venta es obligatoria',
            'venta_id.exists' => 'La venta seleccionada no existe',
            
            'ruc_emisor.size' => 'El RUC del emisor debe tener exactamente 13 caracteres',
            'establecimiento.size' => 'El establecimiento debe tener exactamente 3 caracteres',
            'punto_emision.size' => 'El punto de emisión debe tener exactamente 3 caracteres',
            
            'identificacion_comprador.max' => 'La identificación del comprador no puede exceder 13 caracteres',
            'email_comprador.email' => 'El email del comprador debe ser válido',
            
            'tipo_documento.in' => 'Tipo de documento inválido',
            'ambiente.in' => 'El ambiente debe ser 1 (Pruebas) o 2 (Producción)',
            'tipo_emision.in' => 'El tipo de emisión debe ser 1 (Normal) o 2 (Contingencia)',
            
            'subtotal.numeric' => 'El subtotal debe ser un número válido',
            'subtotal.min' => 'El subtotal no puede ser negativo',
            
            'descuento.numeric' => 'El descuento debe ser un número válido',
            'descuento.min' => 'El descuento no puede ser negativo',
            
            'iva.numeric' => 'El IVA debe ser un número válido',
            'iva.min' => 'El IVA no puede ser negativo',
            
            'total.numeric' => 'El total debe ser un número válido',
            'total.min' => 'El total no puede ser negativo',
        ];
    }

    /**
     * Preparar datos antes de validación
     */
    protected function prepareForValidation()
    {
        // Establecer valores por defecto basados en la configuración del sistema
        $defaults = [
            'establecimiento' => '001',
            'punto_emision' => '001',
            'tipo_documento' => '01', // Factura
            'ambiente' => config('sri.ambiente', '1'), // 1: Pruebas por defecto
            'tipo_emision' => '1', // Emisión normal
            'descuento' => 0,
        ];

        foreach ($defaults as $key => $value) {
            if (!$this->filled($key)) {
                $this->merge([$key => $value]);
            }
        }
    }
}