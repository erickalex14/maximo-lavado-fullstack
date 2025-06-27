<?php

namespace App\Http\Requests\Venta;

use Illuminate\Foundation\Http\FormRequest;

class CreateVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer',
            'items.*.tipo' => 'required|in:automotriz,despensa',
            'items.*.cantidad' => 'required|integer|min:1',
            'cliente_id' => 'nullable|exists:clientes,cliente_id'
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Los items de venta son obligatorios.',
            'items.array' => 'Los items deben ser un arreglo.',
            'items.min' => 'Debe incluir al menos un item para la venta.',
            'items.*.producto_id.required' => 'El ID del producto es obligatorio.',
            'items.*.producto_id.integer' => 'El ID del producto debe ser un número entero.',
            'items.*.tipo.required' => 'El tipo de producto es obligatorio.',
            'items.*.tipo.in' => 'El tipo de producto debe ser automotriz o despensa.',
            'items.*.cantidad.required' => 'La cantidad es obligatoria.',
            'items.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
        ];
    }
}
