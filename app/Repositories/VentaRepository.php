<?php

namespace App\Repositories;

use App\Contracts\VentaRepositoryInterface;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use App\Models\Ingreso;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaRepository implements VentaRepositoryInterface
{
    // METODO PARA OBTENER TODAS LAS VENTAS DE PRODUCTOS AUTOMOTRICES
    public function getVentasAutomotrices(): Collection
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    // METODO PARA OBTENER TODAS LAS VENTAS DE PRODUCTOS DE DESPENSA

    public function getVentasDespensa(): Collection
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    // METODO PARA OBTENER TODAS LAS VENTAS, INCLUYENDO AUTOMOTRICES Y DE DESPENSA

    public function getAllVentas(): Collection
    {
        $ventasAutomotrices = $this->getVentasAutomotrices()->map(function($venta) {
            return [
                'id' => $venta->venta_producto_automotriz_id,
                'fecha' => $venta->fecha,
                'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                'producto' => $venta->productoAutomotriz->nombre,
                'cantidad' => $venta->cantidad,
                'precio_unitario' => $venta->precio_unitario,
                'total' => $venta->total,
                'tipo' => 'automotriz'
            ];
        });

        $ventasDespensa = $this->getVentasDespensa()->map(function($venta) {
            return [
                'id' => $venta->venta_producto_despensa_id,
                'fecha' => $venta->fecha,
                'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                'producto' => $venta->productoDespensa->nombre,
                'cantidad' => $venta->cantidad,
                'precio_unitario' => $venta->precio_unitario,
                'total' => $venta->total,
                'tipo' => 'despensa'
            ];
        });

        return $ventasAutomotrices->merge($ventasDespensa)
            ->sortByDesc('fecha')
            ->values();
    }

    // METODO PARA CREAR UNA VENTA DE PRODUCTO AUTOMOTRIZ 

    public function createVentaAutomotriz(array $data): VentaProductoAutomotriz
    {
        return DB::transaction(function () use ($data) {
            // Crear la venta
            $venta = VentaProductoAutomotriz::create($data);
            
            // Obtener información del producto para la descripción del ingreso
            $producto = ProductoAutomotriz::find($data['producto_id']);
            $cliente = null;
            if (isset($data['cliente_id']) && $data['cliente_id']) {
                $cliente = Cliente::find($data['cliente_id']);
            }
            
            // Crear descripción del ingreso
            $descripcion = 'Venta de ' . $producto->nombre;
            if ($cliente) {
                $descripcion .= ' - Cliente: ' . $cliente->nombre;
            } else {
                $descripcion .= ' - Cliente General';
            }
            
            // Crear el ingreso automáticamente
            $ingreso = Ingreso::create([
                'fecha' => $data['fecha'] ?? now(),
                'tipo' => 'producto_automotriz',
                'referencia_id' => $venta->id,
                'monto' => $data['total'],
                'descripcion' => $descripcion,
            ]);

            // Generar factura automáticamente
            $numeroFactura = $this->generateNumeroFactura();
            $descripcionFactura = 'Factura por venta de ' . $producto->nombre;
            
            $factura = Factura::create([
                'numero_factura' => $numeroFactura,
                'cliente_id' => $data['cliente_id'] ?? null,
                'fecha' => $data['fecha'] ?? now(),
                'descripcion' => $descripcionFactura,
                'total' => $data['total'],
            ]);

            // Crear detalle de factura
            FacturaDetalle::create([
                'factura_id' => $factura->factura_id,
                'venta_producto_automotriz_id' => $venta->venta_producto_automotriz_id,
                'cantidad' => $data['cantidad'],
                'precio_unitario' => $data['precio_unitario'],
                'subtotal' => $data['total'],
            ]);
            
            return $venta->load(['productoAutomotriz', 'cliente']);
        });
    }

    // METODO PARA CREAR UNA VENTA DE PRODUCTO DE DESPENSA

    public function createVentaDespensa(array $data): VentaProductoDespensa
    {
        return DB::transaction(function () use ($data) {
            // Crear la venta
            $venta = VentaProductoDespensa::create($data);
            
            // Obtener información del producto para la descripción del ingreso
            $producto = ProductoDespensa::find($data['producto_id']);
            $cliente = null;
            if (isset($data['cliente_id']) && $data['cliente_id']) {
                $cliente = Cliente::find($data['cliente_id']);
            }
            
            // Crear descripción del ingreso
            $descripcion = 'Venta de ' . $producto->nombre;
            if ($cliente) {
                $descripcion .= ' - Cliente: ' . $cliente->nombre;
            } else {
                $descripcion .= ' - Cliente General';
            }
            
            // Crear el ingreso automáticamente
            $ingreso = Ingreso::create([
                'fecha' => $data['fecha'] ?? now(),
                'tipo' => 'producto_despensa',
                'referencia_id' => $venta->id,
                'monto' => $data['total'],
                'descripcion' => $descripcion,
            ]);

            // Generar factura automáticamente
            $numeroFactura = $this->generateNumeroFactura();
            $descripcionFactura = 'Factura por venta de ' . $producto->nombre;
            
            $factura = Factura::create([
                'numero_factura' => $numeroFactura,
                'cliente_id' => $data['cliente_id'] ?? null,
                'fecha' => $data['fecha'] ?? now(),
                'descripcion' => $descripcionFactura,
                'total' => $data['total'],
            ]);

            // Crear detalle de factura
            FacturaDetalle::create([
                'factura_id' => $factura->factura_id,
                'venta_producto_despensa_id' => $venta->venta_producto_despensa_id,
                'cantidad' => $data['cantidad'],
                'precio_unitario' => $data['precio_unitario'],
                'subtotal' => $data['total'],
            ]);
            
            return $venta->load(['productoDespensa', 'cliente']);
        });
    }

    // METODO PARA OBTENER UNA VENTA DE PRODUCTO AUTOMOTRIZ POR ID

    public function findVentaAutomotrizById(int $id): ?VentaProductoAutomotriz
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])->find($id);
    }

    // METODO PARA OBTENER UNA VENTA DE PRODUCTO DE DESPENSA POR ID

    public function findVentaDespensaById(int $id): ?VentaProductoDespensa
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])->find($id);
    }

    // METODOS PARA ACTUALIZAR VENTAS DE PRODUCTOS AUTOMOTRICES

    public function updateVentaAutomotriz(int $id, array $data): ?VentaProductoAutomotriz
    {
        $venta = VentaProductoAutomotriz::find($id);
        if ($venta) {
            $venta->update($data);
            return $venta->fresh(['productoAutomotriz', 'cliente']);
        }
        return null;
    }

    // METODOS PARA ACTUALIZAR VENTAS DE PRODUCTOS DE DESPENSA

    public function updateVentaDespensa(int $id, array $data): ?VentaProductoDespensa
    {
        $venta = VentaProductoDespensa::find($id);
        if ($venta) {
            $venta->update($data);
            return $venta->fresh(['productoDespensa', 'cliente']);
        }
        return null;
    }

    // METODOS PARA ELIMINAR VENTAS DE PRODUCTOS AUTOMOTRICES Y DE DESPENSA

    // ELIMINAR VENTA DE PRODUCTO AUTOMOTRIZ

    public function deleteVentaAutomotriz(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = VentaProductoAutomotriz::find($id);
            if (!$venta) return false;

            // Soft delete la factura relacionada si existe
            $facturaDetalle = FacturaDetalle::where('venta_producto_automotriz_id', $id)->first();
            if ($facturaDetalle) {
                $factura = $facturaDetalle->factura;
                // Soft delete detalles de factura
                $facturaDetalle->delete();
                // Soft delete factura si no tiene más detalles activos
                if ($factura->detalles()->count() == 0) {
                    $factura->delete();
                }
            }

            // Soft delete el ingreso relacionado si existe
            $ingreso = Ingreso::where('tipo', 'producto_automotriz')
                ->where('referencia_id', $id)
                ->first();
            if ($ingreso) {
                $ingreso->delete();
            }

            // Soft delete la venta
            return $venta->delete();
        });
    }

    // ELIMINAR VENTA DE PRODUCTO DE DESPENSA

    public function deleteVentaDespensa(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = VentaProductoDespensa::find($id);
            if (!$venta) return false;

            // Soft delete la factura relacionada si existe
            $facturaDetalle = FacturaDetalle::where('venta_producto_despensa_id', $id)->first();
            if ($facturaDetalle) {
                $factura = $facturaDetalle->factura;
                // Soft delete detalles de factura
                $facturaDetalle->delete();
                // Soft delete factura si no tiene más detalles activos
                if ($factura->detalles()->count() == 0) {
                    $factura->delete();
                }
            }

            // Soft delete el ingreso relacionado si existe
            $ingreso = Ingreso::where('tipo', 'producto_despensa')
                ->where('referencia_id', $id)
                ->first();
            if ($ingreso) {
                $ingreso->delete();
            }

            // Soft delete la venta
            return $venta->delete();
        });
    }

    // METODOS PARA RESTAURAR VENTAS ELIMINADAS LÓGICAMENTE

    //RESTURAR VENTA DE PRODUCTO AUTOMOTRIZ ELIMINADA LÓGICAMENTE

    public function restoreVentaAutomotriz(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = VentaProductoAutomotriz::onlyTrashed()->findOrFail($id);
            
            $restored = $venta->restore();
            
            if ($restored) {
                // Restaurar el ingreso relacionado
                $ingreso = Ingreso::onlyTrashed()
                    ->where('tipo', 'producto_automotriz')
                    ->where('referencia_id', $id)
                    ->first();
                
                if ($ingreso) {
                    $ingreso->restore();
                }
                
                // Restaurar la factura relacionada
                $facturaDetalle = FacturaDetalle::onlyTrashed()
                    ->where('venta_producto_automotriz_id', $id)
                    ->first();
                    
                if ($facturaDetalle) {
                    $facturaDetalle->restore();
                    $factura = Factura::onlyTrashed()->find($facturaDetalle->factura_id);
                    if ($factura) {
                        $factura->restore();
                    }
                }
            }
            
            return $restored;
        });
    }

    // RESTURAR VENTA DE PRODUCTO DE DESPENSA ELIMINADA LÓGICAMENTE

    public function restoreVentaDespensa(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = VentaProductoDespensa::onlyTrashed()->findOrFail($id);
            
            $restored = $venta->restore();
            
            if ($restored) {
                // Restaurar el ingreso relacionado
                $ingreso = Ingreso::onlyTrashed()
                    ->where('tipo', 'producto_despensa')
                    ->where('referencia_id', $id)
                    ->first();
                
                if ($ingreso) {
                    $ingreso->restore();
                }
                
                // Restaurar la factura relacionada
                $facturaDetalle = FacturaDetalle::onlyTrashed()
                    ->where('venta_producto_despensa_id', $id)
                    ->first();
                    
                if ($facturaDetalle) {
                    $facturaDetalle->restore();
                    $factura = Factura::onlyTrashed()->find($facturaDetalle->factura_id);
                    if ($factura) {
                        $factura->restore();
                    }
                }
            }
            
            return $restored;
        });
    }

    // METODOS PARA OBTENER VENTAS ELIMINADAS LÓGICAMENTE

    // OBTENER VENTAS AUTOMOTRICES ELIMINADAS LÓGICAMENTE

    public function getTrashedVentasAutomotrices(): Collection
    {
        return VentaProductoAutomotriz::onlyTrashed()
            ->with(['productoAutomotriz', 'cliente'])
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    // OBTENER VENTAS DE DESPENSA ELIMINADAS LÓGICAMENTE

    public function getTrashedVentasDespensa(): Collection
    {
        return VentaProductoDespensa::onlyTrashed()
            ->with(['productoDespensa', 'cliente'])
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    // METODOS PARA OBTENER VENTAS POR CLIENTE Y RANGO DE FECHAS

    // OBTENER VENTAS POR CLIENTE ID

    public function getVentasByClienteId(int $clienteId): Collection
    {
        $ventasAutomotrices = VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->where('cliente_id', $clienteId)
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->venta_producto_automotriz_id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente->nombre,
                    'producto' => $venta->productoAutomotriz->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'automotriz'
                ];
            });

        $ventasDespensa = VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->where('cliente_id', $clienteId)
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->venta_producto_despensa_id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente->nombre,
                    'producto' => $venta->productoDespensa->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'despensa'
                ];
            });

        return $ventasAutomotrices->merge($ventasDespensa)
            ->sortByDesc('fecha')
            ->values();
    }

    // OBTENER VENTAS POR RANGO DE FECHAS

    public function getVentasByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        $ventasAutomotrices = VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->venta_producto_automotriz_id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                    'producto' => $venta->productoAutomotriz->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'automotriz'
                ];
            });

        $ventasDespensa = VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->venta_producto_despensa_id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                    'producto' => $venta->productoDespensa->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'despensa'
                ];
            });

        return $ventasAutomotrices->merge($ventasDespensa)
            ->sortByDesc('fecha')
            ->values();
    }

    //OBTENER MÉTRICAS GENERALES DE VENTAS
    
    public function getMetricas(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $ventasHoy = VentaProductoAutomotriz::whereDate('created_at', $today)->count() + 
                    VentaProductoDespensa::whereDate('created_at', $today)->count();

        $ventasMes = VentaProductoAutomotriz::where('created_at', '>=', $thisMonth)->count() + 
                    VentaProductoDespensa::where('created_at', '>=', $thisMonth)->count();

        $ingresoHoy = VentaProductoAutomotriz::whereDate('created_at', $today)->sum('total') + 
                     VentaProductoDespensa::whereDate('created_at', $today)->sum('total');

        $ingresoMes = VentaProductoAutomotriz::where('created_at', '>=', $thisMonth)->sum('total') + 
                     VentaProductoDespensa::where('created_at', '>=', $thisMonth)->sum('total');

        return [
            'ventasHoy' => $ventasHoy,
            'ventasMes' => $ventasMes,
            'ingresoHoy' => $ingresoHoy,
            'ingresoMes' => $ingresoMes
        ];
    }

    // =======================================================
    // MÉTODOS ESPECÍFICOS PARA VENTAS AUTOMOTRICES
    // =======================================================

    public function getAllVentasAutomotrices(): Collection
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function getVentasAutomotricesByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function getMetricasAutomotrices(array $params = []): array
    {
        $query = VentaProductoAutomotriz::query();

        if (isset($params['fecha_inicio']) && isset($params['fecha_fin'])) {
            $query->whereBetween('fecha', [$params['fecha_inicio'], $params['fecha_fin']]);
        }

        $totalVentas = $query->count();
        $totalIngresos = $query->sum('total');
        $promedioVenta = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;

        return [
            'total_ventas' => $totalVentas,
            'total_ingresos' => $totalIngresos,
            'promedio_venta' => $promedioVenta,
            'productos_mas_vendidos' => $this->getProductosAutomotricesMasVendidos($params)
        ];
    }

    private function getProductosAutomotricesMasVendidos(array $params = []): Collection
    {
        $query = VentaProductoAutomotriz::with('productoAutomotriz')
            ->selectRaw('producto_id, SUM(cantidad) as total_cantidad, SUM(total) as total_ingresos')
            ->groupBy('producto_id');

        if (isset($params['fecha_inicio']) && isset($params['fecha_fin'])) {
            $query->whereBetween('fecha', [$params['fecha_inicio'], $params['fecha_fin']]);
        }

        return $query->orderBy('total_cantidad', 'desc')
            ->limit(10)
            ->get();
    }

    // =======================================================
    // MÉTODOS ESPECÍFICOS PARA VENTAS DE DESPENSA
    // =======================================================

    public function getAllVentasDespensa(): Collection
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function getVentasDespensaByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }

    public function getMetricasDespensa(array $params = []): array
    {
        $query = VentaProductoDespensa::query();

        if (isset($params['fecha_inicio']) && isset($params['fecha_fin'])) {
            $query->whereBetween('fecha', [$params['fecha_inicio'], $params['fecha_fin']]);
        }

        $totalVentas = $query->count();
        $totalIngresos = $query->sum('total');
        $promedioVenta = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;

        return [
            'total_ventas' => $totalVentas,
            'total_ingresos' => $totalIngresos,
            'promedio_venta' => $promedioVenta,
            'productos_mas_vendidos' => $this->getProductosDespensaMasVendidos($params)
        ];
    }

    private function getProductosDespensaMasVendidos(array $params = []): Collection
    {
        $query = VentaProductoDespensa::with('productoDespensa')
            ->selectRaw('producto_id, SUM(cantidad) as total_cantidad, SUM(total) as total_ingresos')
            ->groupBy('producto_id');

        if (isset($params['fecha_inicio']) && isset($params['fecha_fin'])) {
            $query->whereBetween('fecha', [$params['fecha_inicio'], $params['fecha_fin']]);
        }

        return $query->orderBy('total_cantidad', 'desc')
            ->limit(10)
            ->get();
    }

    public function getProductosDisponibles(): array
    {
        $automotrices = ProductoAutomotriz::where('stock', '>', 0)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->producto_automotriz_id,
                    'nombre' => $producto->nombre,
                    'precio_venta' => $producto->precio_venta,
                    'stock' => $producto->stock,
                    'tipo' => 'automotriz'
                ];
            });

        $despensa = ProductoDespensa::where('stock', '>', 0)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->producto_despensa_id,
                    'nombre' => $producto->nombre,
                    'precio_venta' => $producto->precio_venta,
                    'stock' => $producto->stock,
                    'tipo' => 'despensa'
                ];
            });

        return [
            'automotrices' => $automotrices,
            'despensa' => $despensa
        ];
    }

    public function getClientes(): Collection
    {
        return Cliente::select('cliente_id as id', 'nombre', 'telefono')
            ->orderBy('nombre')
            ->get();
    }

    public function procesarVentaCompleta(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $ventasCreadas = [];
            $montoTotal = 0;

            foreach ($data['items'] as $item) {
                if ($item['tipo'] === 'automotriz') {
                    $resultado = $this->procesarVentaAutomotrizInternal($item, $data['cliente_id'] ?? null);
                } else {
                    $resultado = $this->procesarVentaDespensaInternal($item, $data['cliente_id'] ?? null);
                }

                $ventasCreadas[] = $resultado['venta'];
                $montoTotal += $resultado['total'];
            }

            return [
                'ventas' => $ventasCreadas,
                'monto_total' => $montoTotal
            ];
        });
    }

    private function procesarVentaAutomotrizInternal(array $item, ?int $clienteId): array
    {
        $producto = ProductoAutomotriz::findOrFail($item['producto_id']);
        
        if ($producto->stock < $item['cantidad']) {
            throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}");
        }

        $precioUnitario = $producto->precio_venta;
        $total = $precioUnitario * $item['cantidad'];

        $venta = $this->createVentaAutomotriz([
            'producto_id' => $item['producto_id'],
            'cliente_id' => $clienteId,
            'cantidad' => $item['cantidad'],
            'precio_unitario' => $precioUnitario,
            'total' => $total,
            'fecha' => now()
        ]);

        // Actualizar stock
        $producto->decrement('stock', $item['cantidad']);

        return [
            'venta' => $venta,
            'total' => $total
        ];
    }

    private function procesarVentaDespensaInternal(array $item, ?int $clienteId): array
    {
        $producto = ProductoDespensa::findOrFail($item['producto_id']);
        
        if ($producto->stock < $item['cantidad']) {
            throw new \Exception("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}");
        }

        $precioUnitario = $producto->precio_venta;
        $total = $precioUnitario * $item['cantidad'];

        $venta = $this->createVentaDespensa([
            'producto_id' => $item['producto_id'],
            'cliente_id' => $clienteId,
            'cantidad' => $item['cantidad'],
            'precio_unitario' => $precioUnitario,
            'total' => $total,
            'fecha' => now()
        ]);

        // Actualizar stock
        $producto->decrement('stock', $item['cantidad']);

        return [
            'venta' => $venta,
            'total' => $total
        ];
    }

    /**
     * Generar número de factura único
     */
    private function generateNumeroFactura(): string
    {
        $prefix = 'FAC-';
        $year = now()->year;
        $month = now()->format('m');
        
        // Buscar el último número de factura del mes actual
        $lastFactura = Factura::where('numero_factura', 'like', $prefix . $year . $month . '%')
            ->orderBy('numero_factura', 'desc')
            ->first();
        
        if ($lastFactura) {
            // Extraer el número secuencial del último número de factura
            $lastNumber = (int) substr($lastFactura->numero_factura, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
