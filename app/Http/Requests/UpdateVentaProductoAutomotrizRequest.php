<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVentaProductoAutomotrizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id' => 'sometimes|integer|exists:productos_automotrices,producto_automotriz_id',
            'cliente_id' => 'nullable|integer|exists:clientes,cliente_id',
            'cantidad' => 'sometimes|integer|min:1',
            'precio_unitario' => 'sometimes|numeric|min:0.01',
            'fecha' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.exists' => 'El producto seleccionado no existe',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
            'precio_unitario.min' => 'El precio unitario debe ser mayor a 0',
            'fecha.date' => 'La fecha debe tener un formato válido',
        ];
    }
}
