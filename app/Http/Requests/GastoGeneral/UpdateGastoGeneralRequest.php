<?php

namespace App\Http\Requests\GastoGeneral;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGastoGeneralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'monto' => 'sometimes|numeric|min:0|max:999999.99',
            'fecha' => 'sometimes|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor o igual a 0.',
            'monto.max' => 'El monto no puede ser mayor a 999,999.99.',
            'fecha.date' => 'La fecha debe ser válida.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
        ];
    }
}
