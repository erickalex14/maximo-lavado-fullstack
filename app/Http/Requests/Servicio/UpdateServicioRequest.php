<?php

namespace App\Http\Requests\Servicio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServicioRequest extends FormRequest
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
        $servicioId = $this->route('id');
        
        return [
            'nombre' => 'sometimes|required|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'tipo_vehiculo_id' => 'sometimes|required|exists:tipos_vehiculos,tipo_vehiculo_id',
            'precio_base' => 'sometimes|required|numeric|min:0|max:999999.99',
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
