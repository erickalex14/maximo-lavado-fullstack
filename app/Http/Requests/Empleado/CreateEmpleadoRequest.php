<?php

namespace App\Http\Requests\Empleado;

use Illuminate\Foundation\Http\FormRequest;

class CreateEmpleadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'cedula' => 'required|string|max:20|unique:empleados,cedula',
            'tipo_salario' => 'required|in:mensual,diario,quincenal,semanal',
            'salario' => 'required|numeric|min:0|max:999999.99',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.string' => 'Los nombres deben ser una cadena de texto.',
            'nombres.max' => 'Los nombres no pueden exceder 255 caracteres.',
            
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',
            'apellidos.max' => 'Los apellidos no pueden exceder 255 caracteres.',
            
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.string' => 'El teléfono debe ser una cadena de texto.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.string' => 'La cédula debe ser una cadena de texto.',
            'cedula.max' => 'La cédula no puede exceder 20 caracteres.',
            'cedula.unique' => 'Esta cédula ya está registrada.',
            
            'tipo_salario.required' => 'El tipo de salario es obligatorio.',
            'tipo_salario.in' => 'El tipo de salario debe ser mensual, diario, quincenal o semanal.',
            
            'salario.required' => 'El salario es obligatorio.',
            'salario.numeric' => 'El salario debe ser un número.',
            'salario.min' => 'El salario debe ser mayor o igual a 0.',
            'salario.max' => 'El salario no puede exceder 999999.99.',
        ];
    }
}
