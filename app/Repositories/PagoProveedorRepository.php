<?php

namespace App\Repositories;

use App\Contracts\PagoProveedorRepositoryInterface;
use App\Models\PagoProveedor;
use Illuminate\Database\Eloquent\Collection;

class PagoProveedorRepository implements PagoProveedorRepositoryInterface
{
    public function getAll(): Collection
    {
        return PagoProveedor::with('proveedor')
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findById(int $id): ?PagoProveedor
    {
        return PagoProveedor::with('proveedor')->find($id);
    }
    
    public function create(array $data): PagoProveedor
    {
        return PagoProveedor::create($data);
    }
    
    public function update(int $id, array $data): ?PagoProveedor
    {
        $pagoProveedor = PagoProveedor::find($id);
        
        if (!$pagoProveedor) {
            return null;
        }
        
        $pagoProveedor->update($data);
        
        return $pagoProveedor->fresh(['proveedor']);
    }
    
    public function delete(int $id): bool
    {
        $pagoProveedor = PagoProveedor::find($id);
        
        if (!$pagoProveedor) {
            return false;
        }
        
        return $pagoProveedor->delete();
    }
    
    public function getByProveedorId(int $proveedorId): Collection
    {
        return PagoProveedor::with('proveedor')
            ->where('proveedor_id', $proveedorId)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return PagoProveedor::with('proveedor')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalPagosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return PagoProveedor::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');
    }
    
    public function getPagosByMes(int $año, int $mes): Collection
    {
        return PagoProveedor::with('proveedor')
            ->whereYear('fecha', $año)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha', 'desc')
            ->get();
    }
}
