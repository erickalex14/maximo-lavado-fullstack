<?php

namespace App\Repositories;

use App\Contracts\GastoGeneralRepositoryInterface;
use App\Models\GastoGeneral;
use Illuminate\Database\Eloquent\Collection;

class GastoGeneralRepository implements GastoGeneralRepositoryInterface
{
    public function getAll(): Collection
    {
        return GastoGeneral::orderBy('fecha', 'desc')->get();
    }
    
    public function findById(int $id): ?GastoGeneral
    {
        return GastoGeneral::find($id);
    }
    
    public function create(array $data): GastoGeneral
    {
        return GastoGeneral::create($data);
    }
    
    public function update(int $id, array $data): ?GastoGeneral
    {
        $gastoGeneral = GastoGeneral::find($id);
        
        if (!$gastoGeneral) {
            return null;
        }
        
        $gastoGeneral->update($data);
        
        return $gastoGeneral->fresh();
    }
    
    public function delete(int $id): bool
    {
        $gastoGeneral = GastoGeneral::find($id);
        
        if (!$gastoGeneral) {
            return false;
        }
        
        return $gastoGeneral->delete();
    }
    
    public function restore(int $id): bool
    {
        $gastoGeneral = GastoGeneral::onlyTrashed()->find($id);
        if ($gastoGeneral) {
            return $gastoGeneral->restore();
        }
        return false;
    }
    
    public function getTrashed(): Collection
    {
        return GastoGeneral::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return GastoGeneral::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalGastosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return GastoGeneral::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');
    }
    
    public function getGastosByMes(int $año, int $mes): Collection
    {
        return GastoGeneral::whereYear('fecha', $año)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha', 'desc')
            ->get();
    }
}
