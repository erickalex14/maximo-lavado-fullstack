<?php

namespace App\Http\Requests\ProductoAutomotriz;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoAutomotrizRequest extends FormRequest
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
        $productoId = $this->route('productoAutomotriz');
        
        return [
            'codigo' => 'sometimes|string|max:50|unique:productos_automotrices,codigo,' . $productoId . ',producto_automotriz_id',
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|nullable|string|max:1000',
            'precio_venta' => 'sometimes|numeric|min:0|max:999999.99',
            'stock' => 'sometimes|integer|min:0',
            'activo' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'codigo.string' => 'El código debe ser una cadena de texto.',
            'codigo.max' => 'El código no puede exceder 50 caracteres.',
            'codigo.unique' => 'Este código ya está registrado.',
            
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
            
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta debe ser mayor o igual a 0.',
            'precio_venta.max' => 'El precio de venta no puede exceder 999999.99.',
            
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock debe ser mayor o igual a 0.',
            
            'activo.boolean' => 'El estado activo debe ser verdadero o falso.',
        ];
    }
}
