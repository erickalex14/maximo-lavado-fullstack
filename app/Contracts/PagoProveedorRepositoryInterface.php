<?php

namespace App\Contracts;

use App\Models\PagoProveedor;
use Illuminate\Database\Eloquent\Collection;

interface PagoProveedorRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?PagoProveedor;
    
    public function create(array $data): array;
    
    public function update(int $id, array $data): ?PagoProveedor;
    
    public function delete(int $id): bool;
    
    public function getByProveedorId(int $proveedorId): Collection;
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    
    public function getTotalPagosByPeriodo(string $fechaInicio, string $fechaFin): float;
    
    public function getPagosByMes(int $año, int $mes): Collection;
}
