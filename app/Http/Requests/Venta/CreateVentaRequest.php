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
            // Datos básicos de la venta (según migración)
            'cliente_id' => 'required|integer|exists:clientes,cliente_id',
            'fecha' => 'nullable|date|before_or_equal:today',
            'descuento' => 'nullable|numeric|min:0|max:999999.99',
            'estado' => 'nullable|string|in:pendiente,completada,cancelada',
            'observaciones' => 'nullable|string|max:1000',
            'usuario_id' => 'nullable|integer|exists:users,id',
            
            // Detalles de la venta (productos/servicios)
            'detalles' => 'required|array|min:1',
            'detalles.*.tipo_item' => [
                'required',
                'string',
                'in:producto_automotriz,producto_despensa,servicio'
            ],
            'detalles.*.item_id' => 'required|integer|min:1',
            'detalles.*.item_nombre' => 'required|string|max:255',
            'detalles.*.item_descripcion' => 'nullable|string|max:1000',
            'detalles.*.cantidad' => 'required|integer|min:1|max:999999',
            'detalles.*.precio_unitario' => 'required|numeric|min:0.01|max:999999.99',
            'detalles.*.descuento' => 'nullable|numeric|min:0|max:999999.99',
            
            // Para servicios específicamente (campos en venta_detalles)
            'detalles.*.vehiculo_id' => 'nullable|integer|exists:vehiculos,vehiculo_id',
            'detalles.*.empleado_id' => 'nullable|integer|exists:empleados,empleado_id',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            
            'fecha.date' => 'La fecha debe ser válida',
            'fecha.before_or_equal' => 'La fecha no puede ser posterior a hoy',
            
            'descuento.numeric' => 'El descuento debe ser un número válido',
            'descuento.min' => 'El descuento no puede ser negativo',
            'descuento.max' => 'El descuento es demasiado alto',
            
            'estado.in' => 'El estado debe ser: pendiente, completada o cancelada',
            
            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres',
            
            'usuario_id.exists' => 'El usuario seleccionado no existe',
            
            'detalles.required' => 'La venta debe tener al menos un producto o servicio',
            'detalles.min' => 'La venta debe tener al menos un detalle',
            
            'detalles.*.tipo_item.required' => 'El tipo de producto/servicio es obligatorio',
            'detalles.*.tipo_item.in' => 'Tipo de item inválido. Debe ser: producto_automotriz, producto_despensa o servicio',
            
            'detalles.*.item_id.required' => 'El ID del producto/servicio es obligatorio',
            'detalles.*.item_id.min' => 'ID de producto/servicio inválido',
            
            'detalles.*.item_nombre.required' => 'El nombre del producto/servicio es obligatorio',
            'detalles.*.item_nombre.max' => 'El nombre no puede exceder 255 caracteres',
            
            'detalles.*.item_descripcion.max' => 'La descripción no puede exceder 1000 caracteres',
            
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
            'detalles.*.cantidad.max' => 'La cantidad es demasiado alta',
            
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio',
            'detalles.*.precio_unitario.min' => 'El precio debe ser mayor a 0',
            'detalles.*.precio_unitario.max' => 'El precio es demasiado alto',
            
            'detalles.*.descuento.numeric' => 'El descuento debe ser un número válido',
            'detalles.*.descuento.min' => 'El descuento no puede ser negativo',
            'detalles.*.descuento.max' => 'El descuento es demasiado alto',
            
            'detalles.*.vehiculo_id.exists' => 'El vehículo seleccionado no existe',
            'detalles.*.empleado_id.exists' => 'El empleado seleccionado no existe',
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
                if ($detalle['tipo_item'] === 'servicio') {
                    // Para servicios de lavado, el vehículo es importante pero puede ser opcional
                    // si se asigna después desde el cliente
                }
                
                // Validar que los IDs existan según el tipo
                $this->validateItemExists($detalle, $index, $validator);
                
                // Calcular subtotal y total si no se proporcionan
                $this->calculateTotals($detalle, $index);
            }
        });
    }

    /**
     * Validar que el item existe según su tipo
     */
    private function validateItemExists(array $detalle, int $index, $validator)
    {
        $type = $detalle['tipo_item'] ?? null;
        $id = $detalle['item_id'] ?? null;
        
        if (!$type || !$id) return;
        
        switch ($type) {
            case 'producto_automotriz':
                if (!\App\Models\ProductoAutomotriz::where('producto_automotriz_id', $id)->where('activo', true)->exists()) {
                    $validator->errors()->add(
                        "detalles.{$index}.item_id",
                        'El producto automotriz no existe o no está activo'
                    );
                }
                break;
                
            case 'producto_despensa':
                if (!\App\Models\ProductoDespensa::where('producto_despensa_id', $id)->where('activo', true)->exists()) {
                    $validator->errors()->add(
                        "detalles.{$index}.item_id",
                        'El producto de despensa no existe o no está activo'
                    );
                }
                break;
                
            case 'servicio':
                if (!\App\Models\Servicio::where('servicio_id', $id)->where('activo', true)->exists()) {
                    $validator->errors()->add(
                        "detalles.{$index}.item_id",
                        'El servicio no existe o no está activo'
                    );
                }
                break;
        }
    }

    /**
     * Calcular totales automáticamente
     */
    private function calculateTotals(array $detalle, int $index)
    {
        $cantidad = $detalle['cantidad'] ?? 0;
        $precioUnitario = $detalle['precio_unitario'] ?? 0;
        $descuento = $detalle['descuento'] ?? 0;
        
        $subtotal = $cantidad * $precioUnitario;
        $total = $subtotal - $descuento;
        
        // Agregar los campos calculados al request
        $this->merge([
            "detalles.{$index}.subtotal" => round($subtotal, 2),
            "detalles.{$index}.total" => round($total, 2)
        ]);
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
        
        // Establecer estado por defecto
        if (!$this->filled('estado')) {
            $this->merge(['estado' => 'pendiente']);
        }
        
        // Establecer descuento por defecto
        if (!$this->filled('descuento')) {
            $this->merge(['descuento' => 0]);
        }
        
        // Establecer usuario_id si está autenticado y no se proporciona
        if (!$this->filled('usuario_id') && auth()->check()) {
            $this->merge(['usuario_id' => auth()->id()]);
        }
        
        // Procesar detalles para establecer valores por defecto
        $detalles = $this->input('detalles', []);
        foreach ($detalles as $index => $detalle) {
            if (!isset($detalle['descuento'])) {
                $detalles[$index]['descuento'] = 0;
            }
        }
        $this->merge(['detalles' => $detalles]);
    }
}
