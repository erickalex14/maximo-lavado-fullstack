<?php

namespace App\Http\Requests\Factura;

use Illuminate\Foundation\Http\FormRequest;

class CreateFacturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_factura' => 'nullable|string|max:255|unique:facturas,numero_factura',
            'cliente_id' => 'required|integer|exists:clientes,cliente_id',
            'fecha' => 'required|date|before_or_equal:today',
            'descripcion' => 'nullable|string|max:1000',
            'total' => 'required|numeric|min:0|max:999999.99',
            
            // Detalles de la factura
            'detalles' => 'required|array|min:1',
            'detalles.*.lavado_id' => 'nullable|integer|exists:lavados,lavado_id',
            'detalles.*.venta_producto_automotriz_id' => 'nullable|integer|exists:venta_producto_automotriz,venta_producto_automotriz_id',
            'detalles.*.venta_producto_despensa_id' => 'nullable|integer|exists:venta_producto_despensa,venta_producto_despensa_id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.subtotal' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'numero_factura.string' => 'El número de factura debe ser texto.',
            'numero_factura.max' => 'El número de factura no puede exceder 255 caracteres.',
            'numero_factura.unique' => 'El número de factura ya existe.',
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.integer' => 'El cliente debe ser un número entero.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser válida.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'total.min' => 'El total debe ser mayor o igual a 0.',
            'total.max' => 'El total no puede ser mayor a 999,999.99.',
            
            // Mensajes para detalles
            'detalles.required' => 'Los detalles de la factura son obligatorios.',
            'detalles.array' => 'Los detalles deben ser un arreglo.',
            'detalles.min' => 'Debe incluir al menos un detalle en la factura.',
            'detalles.*.lavado_id.exists' => 'El lavado especificado no existe.',
            'detalles.*.venta_producto_automotriz_id.exists' => 'La venta de producto automotriz especificada no existe.',
            'detalles.*.venta_producto_despensa_id.exists' => 'La venta de producto de despensa especificada no existe.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en cada detalle.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio en cada detalle.',
            'detalles.*.precio_unitario.min' => 'El precio unitario debe ser mayor o igual a 0.',
            'detalles.*.subtotal.required' => 'El subtotal es obligatorio en cada detalle.',
            'detalles.*.subtotal.min' => 'El subtotal debe ser mayor o igual a 0.',
        ];
    }
}
