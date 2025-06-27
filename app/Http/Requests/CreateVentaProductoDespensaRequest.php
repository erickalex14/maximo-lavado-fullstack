<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVentaProductoDespensaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id' => 'required|integer|exists:productos_despensa,producto_despensa_id',
            'cliente_id' => 'nullable|integer|exists:clientes,cliente_id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0.01',
            'fecha' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required' => 'El producto es obligatorio',
            'producto_id.exists' => 'El producto seleccionado no existe',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'cantidad.required' => 'La cantidad es obligatoria',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
            'precio_unitario.required' => 'El precio unitario es obligatorio',
            'precio_unitario.min' => 'El precio unitario debe ser mayor a 0',
            'fecha.date' => 'La fecha debe tener un formato válido',
        ];
    }
}
