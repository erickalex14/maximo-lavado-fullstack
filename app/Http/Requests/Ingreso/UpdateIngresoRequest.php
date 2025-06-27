<?php

namespace App\Http\Requests\Ingreso;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIngresoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha' => 'sometimes|date|before_or_equal:today',
            'tipo' => 'sometimes|in:lavado,producto_automotriz,producto_despensa',
            'referencia_id' => 'nullable|integer|min:1',
            'monto' => 'sometimes|numeric|min:0|max:999999.99',
            'descripcion' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.date' => 'La fecha debe ser válida.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'tipo.in' => 'El tipo debe ser: lavado, producto_automotriz o producto_despensa.',
            'referencia_id.integer' => 'La referencia debe ser un número entero.',
            'referencia_id.min' => 'La referencia debe ser mayor a 0.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor o igual a 0.',
            'monto.max' => 'El monto no puede ser mayor a 999,999.99.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede exceder 255 caracteres.',
        ];
    }
}
