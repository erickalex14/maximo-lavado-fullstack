<?php

namespace App\Http\Requests\Proveedor;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'monto' => 'sometimes|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'fecha' => 'sometimes|date',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
            'fecha.date' => 'La fecha debe tener un formato válido.',
        ];
    }
}
