<?php

namespace App\Http\Requests\PagoProveedor;

use Illuminate\Foundation\Http\FormRequest;

class CreatePagoProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => 'required|integer|exists:proveedores,proveedor_id',
            'monto' => 'required|numeric|min:0|max:999999.99',
            'fecha' => 'required|date|before_or_equal:today',
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'El proveedor es obligatorio.',
            'proveedor_id.integer' => 'El proveedor debe ser un número entero.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor o igual a 0.',
            'monto.max' => 'El monto no puede ser mayor a 999,999.99.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser válida.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
        ];
    }
}
