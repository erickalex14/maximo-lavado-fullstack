<?php

namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
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
        $clienteId = $this->route('id') ?? $this->route('cliente');

        return [
            'nombre' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clientes', 'email')->ignore($clienteId, 'cliente_id')
            ],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'cedula' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('clientes', 'cedula')->ignore($clienteId, 'cliente_id')
            ]
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
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Ya existe otro cliente con este email.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.unique' => 'Ya existe otro cliente con este teléfono.',
            'direccion.max' => 'La dirección no puede exceder 500 caracteres.'
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

        // Asegurar que activo sea boolean
        if ($this->has('activo')) {
            $this->merge([
                'activo' => filter_var($this->activo, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
