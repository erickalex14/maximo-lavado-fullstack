<?php

namespace App\Http\Requests\Empleado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpleadoRequest extends FormRequest
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
        $empleadoId = $this->route('empleado');
        
        return [
            'nombres' => 'sometimes|string|max:255',
            'apellidos' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|string|max:20',
            'cedula' => 'sometimes|string|max:20|unique:empleados,cedula,' . $empleadoId . ',empleado_id',
            'tipo_salario' => 'sometimes|in:mensual,diario,quincenal,semanal',
            'salario' => 'sometimes|numeric|min:0|max:999999.99',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'nombres.string' => 'Los nombres deben ser una cadena de texto.',
            'nombres.max' => 'Los nombres no pueden exceder 255 caracteres.',
            
            'apellidos.string' => 'Los apellidos deben ser una cadena de texto.',
            'apellidos.max' => 'Los apellidos no pueden exceder 255 caracteres.',
            
            'telefono.string' => 'El teléfono debe ser una cadena de texto.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            
            'cedula.string' => 'La cédula debe ser una cadena de texto.',
            'cedula.max' => 'La cédula no puede exceder 20 caracteres.',
            'cedula.unique' => 'Esta cédula ya está registrada.',
            
            'tipo_salario.in' => 'El tipo de salario debe ser mensual, diario, quincenal o semanal.',
            
            'salario.numeric' => 'El salario debe ser un número.',
            'salario.min' => 'El salario debe ser mayor o igual a 0.',
            'salario.max' => 'El salario no puede exceder 999999.99.',
        ];
    }
}
