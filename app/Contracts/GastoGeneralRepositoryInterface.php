<?php

namespace App\Contracts;

use App\Models\GastoGeneral;
use Illuminate\Database\Eloquent\Collection;

interface GastoGeneralRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?GastoGeneral;
    
    public function create(array $data): GastoGeneral;
    
    public function update(int $id, array $data): ?GastoGeneral;
    
    public function delete(int $id): bool;
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    
    public function getTotalGastosByPeriodo(string $fechaInicio, string $fechaFin): float;
    
    public function getGastosByMes(int $año, int $mes): Collection;
}
