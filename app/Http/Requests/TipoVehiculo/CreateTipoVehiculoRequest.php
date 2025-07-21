<?php

namespace App\Http\Requests\TipoVehiculo;

use Illuminate\Foundation\Http\FormRequest;

class CreateTipoVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100|unique:tipos_vehiculos,nombre',
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del tipo de vehículo es obligatorio.',
            'nombre.unique' => 'Ya existe un tipo de vehículo con ese nombre.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.'
        ];
    }
}
