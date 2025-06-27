<?php

namespace App\Http\Requests\Lavado;

use Illuminate\Foundation\Http\FormRequest;

class CreateLavadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehiculo_id' => 'required|exists:vehiculos,vehiculo_id',
            'empleado_id' => 'required|exists:empleados,empleado_id',
            'fecha' => 'required|date',
            'tipo_lavado' => 'required|string|in:completo,solo_fuera,solo_por_dentro',
            'precio' => 'required|numeric|min:0',
            'pulverizado' => 'boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'vehiculo_id.required' => 'El vehículo es obligatorio.',
            'vehiculo_id.exists' => 'El vehículo seleccionado no existe.',
            'empleado_id.required' => 'El empleado es obligatorio.',
            'empleado_id.exists' => 'El empleado seleccionado no existe.',
            'fecha.required' => 'La fecha del lavado es obligatoria.',
            'fecha.date' => 'La fecha del lavado debe ser válida.',
            'tipo_lavado.required' => 'El tipo de lavado es obligatorio.',
            'tipo_lavado.in' => 'El tipo de lavado debe ser: completo, solo fuera o solo por dentro.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio debe ser mayor a 0.',
            'pulverizado.boolean' => 'El campo pulverizado debe ser verdadero o falso.'
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('pulverizado')) {
            $this->merge(['pulverizado' => false]);
        }

        if (!$this->has('fecha')) {
            $this->merge(['fecha' => now()->format('Y-m-d')]);
        }
    }
}
