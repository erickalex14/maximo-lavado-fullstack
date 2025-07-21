<?php

namespace App\Http\Requests\Servicio;

use Illuminate\Foundation\Http\FormRequest;

class CreateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100|unique:servicios,nombre',
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean',
            'precios' => 'nullable|array',
            'precios.*.tipo_vehiculo_id' => 'required|exists:tipos_vehiculos,tipo_vehiculo_id',
            'precios.*.precio' => 'required|numeric|min:0|max:999999.99'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del servicio es obligatorio.',
            'nombre.unique' => 'Ya existe un servicio con ese nombre.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.',
            'precios.*.tipo_vehiculo_id.required' => 'El tipo de vehículo es obligatorio para cada precio.',
            'precios.*.tipo_vehiculo_id.exists' => 'El tipo de vehículo seleccionado no es válido.',
            'precios.*.precio.required' => 'El precio es obligatorio.',
            'precios.*.precio.numeric' => 'El precio debe ser un número válido.',
            'precios.*.precio.min' => 'El precio debe ser mayor o igual a 0.',
            'precios.*.precio.max' => 'El precio no puede exceder los 999,999.99.'
        ];
    }
}
