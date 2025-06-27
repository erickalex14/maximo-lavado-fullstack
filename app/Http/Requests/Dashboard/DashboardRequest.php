<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limite' => 'nullable|integer|min:1|max:50',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'incluir_alertas' => 'nullable|boolean',
            'incluir_charts' => 'nullable|boolean',
            'incluir_actividad' => 'nullable|boolean',
            'incluir_citas' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'limite.integer' => 'El límite debe ser un número entero.',
            'limite.min' => 'El límite debe ser al menos 1.',
            'limite.max' => 'El límite no puede ser mayor a 50.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.date' => 'La fecha fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha de inicio.'
        ];
    }

    public function prepareForValidation()
    {
        if (!$this->limite) {
            $this->merge(['limite' => 5]);
        }

        // Establecer valores por defecto para los incluir_*
        $this->merge([
            'incluir_alertas' => $this->incluir_alertas ?? true,
            'incluir_charts' => $this->incluir_charts ?? true,
            'incluir_actividad' => $this->incluir_actividad ?? true,
            'incluir_citas' => $this->incluir_citas ?? true
        ]);
    }
}
