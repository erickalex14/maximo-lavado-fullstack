<?php

namespace App\Http\Requests\Venta;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 🛒 CreateVentaRequest - Request unificado para todas las ventas V2
 * 
 * Maneja los 3 tipos de venta del sistema:
 * 1. Solo productos (automotrices/despensa)
 * 2. Solo servicios
 * 3. Mixtas (productos + servicios)
 * 
 * Validación inteligente según el tipo de vendible
 */
class CreateVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos básicos de la venta
            'cliente_id' => 'required|integer|exists:clientes,cliente_id',
            'empleado_id' => 'nullable|integer|exists:empleados,empleado_id',
            'fecha' => 'nullable|date|before_or_equal:today',
            'metodo_pago' => 'nullable|string|in:efectivo,tarjeta,transferencia,credito',
            'observaciones' => 'nullable|string|max:500',
            
            // Detalles de la venta (productos/servicios)
            'detalles' => 'required|array|min:1',
            'detalles.*.vendible_type' => [
                'required',
                'string',
                'in:App\Models\ProductoAutomotriz,App\Models\ProductoDespensa,App\Models\Servicio'
            ],
            'detalles.*.vendible_id' => 'required|integer|min:1',
            'detalles.*.cantidad' => 'required|numeric|min:0.01|max:999999.99',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01|max:999999.99',
            'detalles.*.descripcion' => 'nullable|string|max:255',
            
            // Para servicios específicamente
            'detalles.*.vehiculo_id' => 'nullable|integer|exists:vehiculos,vehiculo_id',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            
            'empleado_id.exists' => 'El empleado seleccionado no existe',
            
            'fecha.date' => 'La fecha debe ser válida',
            'fecha.before_or_equal' => 'La fecha no puede ser posterior a hoy',
            
            'metodo_pago.in' => 'El método de pago debe ser: efectivo, tarjeta, transferencia o crédito',
            
            'detalles.required' => 'La venta debe tener al menos un producto o servicio',
            'detalles.min' => 'La venta debe tener al menos un detalle',
            
            'detalles.*.vendible_type.required' => 'El tipo de producto/servicio es obligatorio',
            'detalles.*.vendible_type.in' => 'Tipo de vendible inválido',
            
            'detalles.*.vendible_id.required' => 'El ID del producto/servicio es obligatorio',
            'detalles.*.vendible_id.min' => 'ID de producto/servicio inválido',
            
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
            'detalles.*.cantidad.max' => 'La cantidad es demasiado alta',
            
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio',
            'detalles.*.precio_unitario.min' => 'El precio debe ser mayor a 0',
            'detalles.*.precio_unitario.max' => 'El precio es demasiado alto',
            
            'detalles.*.descripcion.max' => 'La descripción no puede exceder 255 caracteres',
            
            'detalles.*.vehiculo_id.exists' => 'El vehículo seleccionado no existe',
        ];
    }

    /**
     * Validación adicional después de las reglas básicas
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $detalles = $this->input('detalles', []);
            
            foreach ($detalles as $index => $detalle) {
                // Validar que servicios tengan vehículo si es requerido
                if ($detalle['vendible_type'] === 'App\Models\Servicio') {
                    // Para servicios de lavado, el vehículo es importante
                    // Pero puede ser opcional si se asigna después
                }
                
                // Validar que los IDs existan según el tipo
                $this->validateVendibleExists($detalle, $index, $validator);
            }
        });
    }

    /**
     * Validar que el vendible existe según su tipo
     */
    private function validateVendibleExists(array $detalle, int $index, $validator)
    {
        $type = $detalle['vendible_type'] ?? null;
        $id = $detalle['vendible_id'] ?? null;
        
        if (!$type || !$id) return;
        
        switch ($type) {
            case 'App\Models\ProductoAutomotriz':
                if (!\App\Models\ProductoAutomotriz::where('producto_automotriz_id', $id)->where('activo', true)->exists()) {
                    $validator->errors()->add(
                        "detalles.{$index}.vendible_id",
                        'El producto automotriz no existe o no está activo'
                    );
                }
                break;
                
            case 'App\Models\ProductoDespensa':
                if (!\App\Models\ProductoDespensa::where('producto_despensa_id', $id)->where('activo', true)->exists()) {
                    $validator->errors()->add(
                        "detalles.{$index}.vendible_id",
                        'El producto de despensa no existe o no está activo'
                    );
                }
                break;
                
            case 'App\Models\Servicio':
                if (!\App\Models\Servicio::where('servicio_id', $id)->where('activo', true)->exists()) {
                    $validator->errors()->add(
                        "detalles.{$index}.vendible_id",
                        'El servicio no existe o no está activo'
                    );
                }
                break;
        }
    }

    /**
     * Preparar datos antes de validación
     */
    protected function prepareForValidation()
    {
        // Si no se proporciona fecha, usar la fecha actual
        if (!$this->filled('fecha')) {
            $this->merge(['fecha' => now()->format('Y-m-d')]);
        }
        
        // Asegurar que método de pago tenga un valor por defecto
        if (!$this->filled('metodo_pago')) {
            $this->merge(['metodo_pago' => 'efectivo']);
        }
    }
}
