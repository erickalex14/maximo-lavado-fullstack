<?php

namespace App\Contracts;

use App\Models\Egreso;
use Illuminate\Database\Eloquent\Collection;

interface EgresoRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?Egreso;
    
    public function create(array $data): Egreso;
    
    public function update(int $id, array $data): ?Egreso;
    
    public function delete(int $id): bool;
    
    public function getByTipo(string $tipo): Collection;
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    
    public function getTotalEgresosByPeriodo(string $fechaInicio, string $fechaFin): float;
    
    public function getEgresosByMes(int $año, int $mes): Collection;
}
