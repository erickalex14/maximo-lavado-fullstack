<?php

namespace App\Http\Requests\Proveedor;

use Illuminate\Foundation\Http\FormRequest;

class CreateProveedorRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:proveedores,email',
            'descripcion' => 'nullable|string|max:1000',
            'telefono' => 'nullable|string|max:20',
            'deuda_pendiente' => 'nullable|numeric|min:0|max:999999.99',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            
            'email.email' => 'El email debe tener un formato válido.',
            'email.max' => 'El email no puede exceder 255 caracteres.',
            'email.unique' => 'Este email ya está registrado.',
            
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
            
            'telefono.string' => 'El teléfono debe ser una cadena de texto.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            
            'deuda_pendiente.numeric' => 'La deuda pendiente debe ser un número.',
            'deuda_pendiente.min' => 'La deuda pendiente debe ser mayor o igual a 0.',
            'deuda_pendiente.max' => 'La deuda pendiente no puede exceder 999999.99.',
        ];
    }
}
