<?php

namespace App\Http\Requests\PagoProveedor;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => 'sometimes|integer|exists:proveedores,proveedor_id',
            'monto' => 'sometimes|numeric|min:0|max:999999.99',
            'fecha' => 'sometimes|date|before_or_equal:today',
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.integer' => 'El proveedor debe ser un número entero.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor o igual a 0.',
            'monto.max' => 'El monto no puede ser mayor a 999,999.99.',
            'fecha.date' => 'La fecha debe ser válida.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
        ];
    }
}
