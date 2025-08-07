<?php

namespace App\Repositories;

use App\Contracts\IngresoRepositoryInterface;
use App\Models\Ingreso;
use Illuminate\Database\Eloquent\Collection;

class IngresoRepository implements IngresoRepositoryInterface
{
    public function getAll(): Collection
    {
        return Ingreso::with(['lavado', 'venta'])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findById(int $id): ?Ingreso
    {
        return Ingreso::with(['lavado', 'venta'])
            ->find($id);
    }
    
    public function create(array $data): Ingreso
    {
        return Ingreso::create($data);
    }
    
    public function update(int $id, array $data): ?Ingreso
    {
        $ingreso = Ingreso::find($id);
        
        if (!$ingreso) {
            return null;
        }
        
        $ingreso->update($data);
        
        return $ingreso->fresh();
    }
    
    public function delete(int $id): bool
    {
        $ingreso = Ingreso::find($id);
        
        if (!$ingreso) {
            return false;
        }
        
        return $ingreso->delete();
    }
    
    public function restore(int $id): bool
    {
        $ingreso = Ingreso::onlyTrashed()->find($id);
        if ($ingreso) {
            return $ingreso->restore();
        }
        return false;
    }
    
    public function getTrashed(): Collection
    {
        return Ingreso::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();
    }
    
    public function getByTipo(string $tipo): Collection
    {
        return Ingreso::where('tipo', $tipo)
            ->with(['lavado', 'venta'])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->with(['lavado', 'venta'])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalIngresosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');
    }
    
    public function getIngresosByMes(int $año, int $mes): Collection
    {
        return Ingreso::whereYear('fecha', $año)
            ->whereMonth('fecha', $mes)
            ->with(['lavado', 'venta'])
            ->orderBy('fecha', 'desc')
            ->get();
    }
}
