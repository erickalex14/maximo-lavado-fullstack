<?php

namespace App\Contracts;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Collection;

interface EmpleadoRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?Empleado;
    
    public function create(array $data): Empleado;
    
    public function update(int $id, array $data): ?Empleado;
    
    public function delete(int $id): bool;
    
    public function getEmpleadosWithLavados(): Collection;
    
    public function countLavadosByEmpleadoAndDate(int $empleadoId, string $fecha): int;
    
    public function countLavadosByEmpleadoAndWeek(int $empleadoId, string $fecha): int;
    
    public function countLavadosByEmpleadoAndMonth(int $empleadoId, int $anio, int $mes): int;
}
