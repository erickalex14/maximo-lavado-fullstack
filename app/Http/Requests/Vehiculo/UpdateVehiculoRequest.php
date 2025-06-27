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
            'tipo' => 'sometimes|string|in:moto,camioneta,auto_pequeno,auto_mediano',
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
            'tipo.in' => 'El tipo de vehículo debe ser: moto, camioneta, auto pequeño o auto mediano.',
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
