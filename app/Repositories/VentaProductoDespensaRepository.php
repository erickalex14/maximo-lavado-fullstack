<?php

namespace App\Repositories;

use App\Contracts\VentaProductoDespensaRepositoryInterface;
use App\Models\VentaProductoDespensa;
use App\Models\Ingreso;
use App\Models\ProductoDespensa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VentaProductoDespensaRepository implements VentaProductoDespensaRepositoryInterface
{
    public function getAll(): Collection
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findById(int $id): ?VentaProductoDespensa
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->find($id);
    }
    
    public function create(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // Verificar que el producto existe y tiene stock suficiente
                $producto = ProductoDespensa::find($data['producto_id']);
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
                $venta = VentaProductoDespensa::create($data);
                
                // Crear el ingreso correspondiente
                $ingreso = Ingreso::create([
                    'fecha' => now()->format('Y-m-d'),
                    'tipo' => 'producto_despensa',
                    'referencia_id' => $venta->id,
                    'monto' => $venta->total,
                    'descripcion' => 'Venta de ' . $producto->nombre . ' (Cantidad: ' . $data['cantidad'] . ')',
                ]);
                
                // Actualizar stock del producto
                $producto->decrement('stock', $data['cantidad']);
                
                return [
                    'success' => true,
                    'venta' => $venta->load(['productoDespensa', 'cliente']),
                    'ingreso' => $ingreso,
                    'producto' => $producto->fresh()
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al crear la venta: ' . $e->getMessage()];
        }
    }
    
    public function update(int $id, array $data): ?VentaProductoDespensa
    {
        $venta = VentaProductoDespensa::find($id);
        
        if (!$venta) {
            return null;
        }
        
        $venta->update($data);
        
        return $venta->fresh(['productoDespensa', 'cliente']);
    }
    
    public function delete(int $id): bool
    {
        $venta = VentaProductoDespensa::find($id);
        
        if (!$venta) {
            return false;
        }
        
        return $venta->delete();
    }
    
    public function getByProductoId(int $productoId): Collection
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->where('producto_id', $productoId)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByClienteId(int $clienteId): Collection
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->where('cliente_id', $clienteId)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalVentasByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return VentaProductoDespensa::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('total');
    }
}
