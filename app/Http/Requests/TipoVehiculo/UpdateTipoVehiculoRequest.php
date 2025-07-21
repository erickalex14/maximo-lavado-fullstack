<?php

namespace App\Http\Requests\TipoVehiculo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTipoVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoVehiculoId = $this->route('id');
        
        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tipos_vehiculos', 'nombre')->ignore($tipoVehiculoId, 'tipo_vehiculo_id')
            ],
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del tipo de vehículo es obligatorio.',
            'nombre.unique' => 'Ya existe otro tipo de vehículo con ese nombre.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.'
        ];
    }
}
