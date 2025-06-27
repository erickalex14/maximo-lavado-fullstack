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
            'cliente_id' => 'sometimes|exists:clientes,id',
            'vehiculo_id' => 'sometimes|exists:vehiculos,id',
            'empleado_id' => 'sometimes|exists:empleados,id',
            'tipo_lavado' => 'sometimes|string|in:basico,completo,premium,encerado',
            'precio' => 'sometimes|numeric|min:0',
            'fecha_lavado' => 'sometimes|date',
            'observaciones' => 'nullable|string|max:1000',
            'estado' => 'sometimes|string|in:pendiente,en_proceso,completado,cancelado'
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'vehiculo_id.exists' => 'El vehículo seleccionado no existe.',
            'empleado_id.exists' => 'El empleado seleccionado no existe.',
            'tipo_lavado.in' => 'El tipo de lavado debe ser: básico, completo, premium o encerado.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio debe ser mayor a 0.',
            'fecha_lavado.date' => 'La fecha del lavado debe ser válida.',
            'estado.in' => 'El estado debe ser: pendiente, en proceso, completado o cancelado.'
        ];
    }
}
