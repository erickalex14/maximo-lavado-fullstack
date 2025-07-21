<?php

namespace App\Http\Requests\Venta;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 🛒 UpdateVentaRequest - Request unificado para actualizar ventas V2
 * 
 * Hereda todas las validaciones de CreateVentaRequest pero hace
 * algunos campos opcionales para actualizaciones parciales
 */
class UpdateVentaRequest extends CreateVentaRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Para actualizaciones, algunos campos son opcionales
        $rules['cliente_id'] = 'nullable|integer|exists:clientes,cliente_id';
        $rules['detalles'] = 'nullable|array|min:1';
        
        return $rules;
    }

    public function messages(): array
    {
        $messages = parent::messages();
        
        // Personalizar mensajes para actualización
        $messages['cliente_id.required'] = 'El cliente es obligatorio para actualizar la venta';
        
        return $messages;
    }

    /**
     * Validación adicional para actualizaciones
     */
    public function withValidator($validator)
    {
        // Llamar a la validación padre
        parent::withValidator($validator);
        
        $validator->after(function ($validator) {
            // Validaciones específicas para updates pueden ir aquí
            // Por ejemplo, verificar si la venta ya tiene factura autorizada
        });
    }
}
