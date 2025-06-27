<?php

namespace App\Http\Requests\Balance;

use Illuminate\Foundation\Http\FormRequest;

class BalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => 'nullable|date|before_or_equal:fecha_fin',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'año' => 'nullable|integer|min:2020|max:' . (date('Y') + 5),
            'dias_proyeccion' => 'nullable|integer|min:1|max:365',
            'incluir_proyeccion' => 'nullable|boolean',
            'incluir_comparativo' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_inicio.before_or_equal' => 'La fecha de inicio debe ser anterior o igual a la fecha fin.',
            'fecha_fin.date' => 'La fecha fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser posterior o igual a la fecha de inicio.',
            'año.integer' => 'El año debe ser un número entero.',
            'año.min' => 'El año debe ser mayor o igual a 2020.',
            'año.max' => 'El año no puede ser mayor a ' . (date('Y') + 5) . '.',
            'dias_proyeccion.integer' => 'Los días de proyección deben ser un número entero.',
            'dias_proyeccion.min' => 'Los días de proyección deben ser al menos 1.',
            'dias_proyeccion.max' => 'Los días de proyección no pueden ser más de 365.',
        ];
    }

    public function prepareForValidation()
    {
        // Si no se proporcionan fechas, usar el mes actual
        if (!$this->fecha_inicio && !$this->fecha_fin) {
            $this->merge([
                'fecha_inicio' => now()->startOfMonth()->format('Y-m-d'),
                'fecha_fin' => now()->endOfMonth()->format('Y-m-d')
            ]);
        }

        // Si no se proporciona año, usar el año actual
        if (!$this->año) {
            $this->merge(['año' => now()->year]);
        }
    }
}
