<?php

namespace App\Http\Requests\Servicio;

use Illuminate\Foundation\Http\FormRequest;

class CreateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'tipo_vehiculo_id' => 'required|exists:tipos_vehiculos,tipo_vehiculo_id',
            'precio_base' => 'required|numeric|min:0|max:999999.99',
            'activo' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del servicio es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.',
            'tipo_vehiculo_id.required' => 'El tipo de vehículo es obligatorio.',
            'tipo_vehiculo_id.exists' => 'El tipo de vehículo seleccionado no es válido.',
            'precio_base.required' => 'El precio base es obligatorio.',
            'precio_base.numeric' => 'El precio base debe ser un número válido.',
            'precio_base.min' => 'El precio base debe ser mayor o igual a 0.',
            'precio_base.max' => 'El precio base no puede exceder los 999,999.99.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.'
        ];
    }
}
