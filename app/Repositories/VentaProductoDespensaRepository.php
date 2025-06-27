<?php

namespace App\Repositories;

use App\Contracts\VentaProductoDespensaRepositoryInterface;
use App\Models\VentaProductoDespensa;
use Illuminate\Database\Eloquent\Collection;

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
    
    public function create(array $data): VentaProductoDespensa
    {
        return VentaProductoDespensa::create($data);
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
