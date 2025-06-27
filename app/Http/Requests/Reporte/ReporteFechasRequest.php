<?php

namespace App\Http\Requests\Reporte;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ReporteFechasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => 'required|date|before_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio|before_or_equal:today',
            'formato' => 'sometimes|in:json,pdf,excel',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_inicio.before_or_equal' => 'La fecha de inicio no puede ser futura.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'fecha_fin.before_or_equal' => 'La fecha de fin no puede ser futura.',
            'formato.in' => 'El formato debe ser json, pdf o excel.',
        ];
    }

    protected function prepareForValidation()
    {
        // Si no se proporciona formato, establecer por defecto
        if (!$this->has('formato')) {
            $this->merge(['formato' => 'json']);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->fecha_inicio && $this->fecha_fin) {
                $inicio = Carbon::parse($this->fecha_inicio);
                $fin = Carbon::parse($this->fecha_fin);
                
                // Validar que el período no sea mayor a 1 año
                if ($inicio->diffInDays($fin) > 365) {
                    $validator->errors()->add('fecha_fin', 'El período no puede ser mayor a 365 días.');
                }
            }
        });
    }
}
