<?php

namespace App\Contracts;

use App\Models\VentaProductoDespensa;
use Illuminate\Database\Eloquent\Collection;

interface VentaProductoDespensaRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?VentaProductoDespensa;
    
    public function create(array $data): array;
    
    public function update(int $id, array $data): ?VentaProductoDespensa;
    
    public function delete(int $id): bool;
    
    public function getByProductoId(int $productoId): Collection;
    
    public function getByClienteId(int $clienteId): Collection;
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    
    public function getTotalVentasByPeriodo(string $fechaInicio, string $fechaFin): float;
}
