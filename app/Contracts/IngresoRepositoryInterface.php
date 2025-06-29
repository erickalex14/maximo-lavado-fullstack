<?php

namespace App\Contracts;

use App\Models\Ingreso;
use Illuminate\Database\Eloquent\Collection;

interface IngresoRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?Ingreso;
    
    public function create(array $data): Ingreso;
    
    public function update(int $id, array $data): ?Ingreso;
    
    public function delete(int $id): bool;
    
    public function restore(int $id): bool;
    
    public function getTrashed(): Collection;
    
    public function getByTipo(string $tipo): Collection;
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    
    public function getTotalIngresosByPeriodo(string $fechaInicio, string $fechaFin): float;
    
    public function getIngresosByMes(int $año, int $mes): Collection;
}
