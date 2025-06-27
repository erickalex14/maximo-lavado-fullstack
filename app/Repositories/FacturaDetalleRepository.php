<?php

namespace App\Repositories;

use App\Contracts\FacturaDetalleRepositoryInterface;
use App\Models\FacturaDetalle;
use Illuminate\Database\Eloquent\Collection;

class FacturaDetalleRepository implements FacturaDetalleRepositoryInterface
{
    public function getAll(): Collection
    {
        return FacturaDetalle::with(['factura', 'lavado', 'ventaProductoAutomotriz', 'ventaProductoDespensa'])
            ->get();
    }
    
    public function findById(int $id): ?FacturaDetalle
    {
        return FacturaDetalle::with(['factura', 'lavado', 'ventaProductoAutomotriz', 'ventaProductoDespensa'])
            ->find($id);
    }
    
    public function create(array $data): FacturaDetalle
    {
        return FacturaDetalle::create($data);
    }
    
    public function update(int $id, array $data): ?FacturaDetalle
    {
        $facturaDetalle = FacturaDetalle::find($id);
        
        if (!$facturaDetalle) {
            return null;
        }
        
        $facturaDetalle->update($data);
        
        return $facturaDetalle->fresh(['factura', 'lavado', 'ventaProductoAutomotriz', 'ventaProductoDespensa']);
    }
    
    public function delete(int $id): bool
    {
        $facturaDetalle = FacturaDetalle::find($id);
        
        if (!$facturaDetalle) {
            return false;
        }
        
        return $facturaDetalle->delete();
    }
    
    public function getByFacturaId(int $facturaId): Collection
    {
        return FacturaDetalle::with(['lavado', 'ventaProductoAutomotriz', 'ventaProductoDespensa'])
            ->where('factura_id', $facturaId)
            ->get();
    }
    
    public function deleteByFacturaId(int $facturaId): bool
    {
        return FacturaDetalle::where('factura_id', $facturaId)->delete() > 0;
    }
}
