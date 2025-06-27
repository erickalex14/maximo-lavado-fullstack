<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->route('id');
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => 'sometimes|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'email.email' => 'El email debe ser válido.',
            'email.max' => 'El email no puede exceder 255 caracteres.',
            'email.unique' => 'El email ya está registrado por otro usuario.',
            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}
