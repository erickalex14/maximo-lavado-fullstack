<?php

namespace App\Http\Requests\Proveedor;

use Illuminate\Foundation\Http\FormRequest;

class CreatePagoRequest extends FormRequest
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
            'proveedor_id' => 'required|integer|exists:proveedores,proveedor_id',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'fecha' => 'nullable|date',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'El ID del proveedor es obligatorio.',
            'proveedor_id.exists' => 'El proveedor especificado no existe.',
            'monto.required' => 'El monto del pago es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
            'fecha.date' => 'La fecha debe tener un formato válido.',
        ];
    }
}
