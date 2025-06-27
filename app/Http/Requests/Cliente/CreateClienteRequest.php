<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateClienteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustar según las políticas de autorización
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clientes,email|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'cedula' => 'nullable|string|unique:clientes,cedula|max:20'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Ya existe un cliente con este email.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'direccion.max' => 'La dirección no puede exceder 500 caracteres.',
            'cedula.unique' => 'Ya existe un cliente con esta cédula.',
            'cedula.max' => 'La cédula no puede exceder 20 caracteres.'
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar formato del teléfono
        if ($this->telefono) {
            $this->merge([
                'telefono' => preg_replace('/[^0-9]/', '', $this->telefono)
            ]);
        }

        // Limpiar formato de la cédula
        if ($this->cedula) {
            $this->merge([
                'cedula' => preg_replace('/[^0-9]/', '', $this->cedula)
            ]);
        }
    }
}
