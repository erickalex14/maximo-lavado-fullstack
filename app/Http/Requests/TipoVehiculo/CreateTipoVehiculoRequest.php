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
            'requiere_matricula' => 'boolean',
            'activo' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del tipo de vehículo es obligatorio.',
            'nombre.unique' => 'Ya existe un tipo de vehículo con ese nombre.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.',
            'requiere_matricula.boolean' => 'El campo requiere matrícula debe ser verdadero o falso.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'requiere_matricula' => $this->boolean('requiere_matricula'),
            'activo' => $this->boolean('activo', true), // Default true para activo
        ]);
    }
}
