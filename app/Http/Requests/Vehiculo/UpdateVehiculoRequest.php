<?php

namespace App\Http\Requests\Vehiculo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vehiculoId = $this->route('id') ?? $this->route('vehiculo');

        return [
            'cliente_id' => 'sometimes|exists:clientes,cliente_id',
            'tipo_vehiculo_id' => 'sometimes|exists:tipos_vehiculos,tipo_vehiculo_id',
            'matricula' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('vehiculos', 'matricula')->ignore($vehiculoId, 'vehiculo_id')
            ],
            'descripcion' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'tipo_vehiculo_id.exists' => 'El tipo de vehículo seleccionado no existe.',
            'matricula.unique' => 'Ya existe otro vehículo con esta matrícula.',
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
