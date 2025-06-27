<?php

namespace App\Http\Requests\ProductoDespensa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoDespensaRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|nullable|string|max:1000',
            'precio_venta' => 'sometimes|numeric|min:0|max:999999.99',
            'stock' => 'sometimes|integer|min:0',
            'activo' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
            
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta debe ser mayor o igual a 0.',
            'precio_venta.max' => 'El precio de venta no puede exceder 999999.99.',
            
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock debe ser mayor o igual a 0.',
            
            'activo.boolean' => 'El estado activo debe ser verdadero o falso.',
        ];
    }
}
