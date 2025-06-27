<?php

namespace App\Repositories;

use App\Contracts\EgresoRepositoryInterface;
use App\Models\Egreso;
use Illuminate\Database\Eloquent\Collection;

class EgresoRepository implements EgresoRepositoryInterface
{
    public function getAll(): Collection
    {
        return Egreso::orderBy('fecha', 'desc')->get();
    }
    
    public function findById(int $id): ?Egreso
    {
        return Egreso::find($id);
    }
    
    public function create(array $data): Egreso
    {
        return Egreso::create($data);
    }
    
    public function update(int $id, array $data): ?Egreso
    {
        $egreso = Egreso::find($id);
        
        if (!$egreso) {
            return null;
        }
        
        $egreso->update($data);
        
        return $egreso->fresh();
    }
    
    public function delete(int $id): bool
    {
        $egreso = Egreso::find($id);
        
        if (!$egreso) {
            return false;
        }
        
        return $egreso->delete();
    }
    
    public function getByTipo(string $tipo): Collection
    {
        return Egreso::where('tipo', $tipo)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalEgresosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');
    }
    
    public function getEgresosByMes(int $año, int $mes): Collection
    {
        return Egreso::whereYear('fecha', $año)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha', 'desc')
            ->get();
    }
}
