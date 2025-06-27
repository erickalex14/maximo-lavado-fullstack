<?php

namespace App\Repositories;

use App\Contracts\VentaProductoAutomotrizRepositoryInterface;
use App\Models\VentaProductoAutomotriz;
use Illuminate\Database\Eloquent\Collection;

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
    
    public function create(array $data): VentaProductoAutomotriz
    {
        return VentaProductoAutomotriz::create($data);
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
