<?php

namespace App\Repositories;

use App\Contracts\FacturaRepositoryInterface;
use App\Models\Factura;
use Illuminate\Database\Eloquent\Collection;

class FacturaRepository implements FacturaRepositoryInterface
{
    public function getAll(): Collection
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findById(int $id): ?Factura
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->find($id);
    }
    
    public function create(array $data): Factura
    {
        return Factura::create($data);
    }
    
    public function update(int $id, array $data): ?Factura
    {
        $factura = Factura::find($id);
        
        if (!$factura) {
            return null;
        }
        
        $factura->update($data);
        
        return $factura->fresh(['cliente', 'facturaDetalles']);
    }
    
    public function delete(int $id): bool
    {
        $factura = Factura::find($id);
        
        if (!$factura) {
            return false;
        }
        
        return $factura->delete();
    }
    
    public function getByClienteId(int $clienteId): Collection
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->where('cliente_id', $clienteId)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalFacturasByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return Factura::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('total');
    }
    
    public function getFacturasByMes(int $año, int $mes): Collection
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->whereYear('fecha', $año)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findByNumeroFactura(string $numeroFactura): ?Factura
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->where('numero_factura', $numeroFactura)
            ->first();
    }
}
