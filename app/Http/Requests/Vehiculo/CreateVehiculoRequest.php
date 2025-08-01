<?php

namespace App\Http\Requests\Vehiculo;

use Illuminate\Foundation\Http\FormRequest;

class CreateVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,cliente_id',
            'tipo_vehiculo_id' => 'required|exists:tipos_vehiculos,tipo_vehiculo_id',
            'matricula' => 'nullable|string|unique:vehiculos,matricula|max:20',
            'descripcion' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'tipo_vehiculo_id.required' => 'El tipo de vehículo es obligatorio.',
            'tipo_vehiculo_id.exists' => 'El tipo de vehículo seleccionado no existe.',
            'matricula.unique' => 'Ya existe un vehículo con esta matrícula.',
            'matricula.max' => 'La matrícula no puede exceder 20 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres.'
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->matricula) {
            $this->merge([
                'matricula' => strtoupper(trim($this->matricula))
            ]);
        }
    }
}
