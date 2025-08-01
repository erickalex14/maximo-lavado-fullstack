<?php

namespace App\Http\Requests\Lavado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLavadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehiculo_id' => 'sometimes|exists:vehiculos,vehiculo_id',
            'empleado_id' => 'sometimes|exists:empleados,empleado_id',
            'servicio_id' => 'nullable|exists:servicios,servicio_id',
            'fecha' => 'sometimes|date',
            'tipo_lavado' => 'sometimes|string|in:completo,solo_fuera,solo_por_dentro',
            'precio' => 'sometimes|numeric|min:0',
            'pulverizado' => 'sometimes|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'vehiculo_id.exists' => 'El vehículo seleccionado no existe.',
            'empleado_id.exists' => 'El empleado seleccionado no existe.',
            'servicio_id.exists' => 'El servicio seleccionado no existe.',
            'fecha.date' => 'La fecha del lavado debe ser válida.',
            'tipo_lavado.in' => 'El tipo de lavado debe ser: completo, solo fuera o solo por dentro.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio debe ser mayor a 0.',
            'pulverizado.boolean' => 'El campo pulverizado debe ser verdadero o falso.'
        ];
    }
}
