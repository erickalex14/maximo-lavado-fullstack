<?php

namespace App\Repositories;

use App\Contracts\VentaProductoAutomotrizRepositoryInterface;
use App\Models\VentaProductoAutomotriz;
use App\Models\Ingreso;
use App\Models\ProductoAutomotriz;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VentaProductoAutomotrizRepository implements VentaProductoAutomotrizRepositoryInterface
{
    public function getAll(): Collection
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findById(int $id): ?VentaProductoAutomotriz
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->find($id);
    }
    
    public function create(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // Verificar que el producto existe y tiene stock suficiente
                $producto = ProductoAutomotriz::find($data['producto_id']);
                if (!$producto) {
                    return ['success' => false, 'message' => 'Producto no encontrado'];
                }
                
                if ($producto->stock < $data['cantidad']) {
                    return ['success' => false, 'message' => 'Stock insuficiente'];
                }
                
                // Calcular el total
                $data['total'] = $data['cantidad'] * $data['precio_unitario'];
                $data['fecha'] = $data['fecha'] ?? now();
                
                // Crear la venta
                $venta = VentaProductoAutomotriz::create($data);
                
                // Crear el ingreso correspondiente
                $ingreso = Ingreso::create([
                    'fecha' => now()->format('Y-m-d'),
                    'tipo' => 'producto_automotriz',
                    'referencia_id' => $venta->id,
                    'monto' => $venta->total,
                    'descripcion' => 'Venta de ' . $producto->nombre . ' (Cantidad: ' . $data['cantidad'] . ')',
                ]);
                
                // Actualizar stock del producto
                $producto->decrement('stock', $data['cantidad']);
                
                return [
                    'success' => true,
                    'venta' => $venta->load(['productoAutomotriz', 'cliente']),
                    'ingreso' => $ingreso,
                    'producto' => $producto->fresh()
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al crear la venta: ' . $e->getMessage()];
        }
    }
    
    public function update(int $id, array $data): ?VentaProductoAutomotriz
    {
        $venta = VentaProductoAutomotriz::find($id);
        
        if (!$venta) {
            return null;
        }
        
        $venta->update($data);
        
        return $venta->fresh(['productoAutomotriz', 'cliente']);
    }
    
    public function delete(int $id): bool
    {
        $venta = VentaProductoAutomotriz::find($id);
        
        if (!$venta) {
            return false;
        }
        
        return $venta->delete();
    }
    
    public function getByProductoId(int $productoId): Collection
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->where('producto_id', $productoId)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByClienteId(int $clienteId): Collection
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->where('cliente_id', $clienteId)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalVentasByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return VentaProductoAutomotriz::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('total');
    }
}
