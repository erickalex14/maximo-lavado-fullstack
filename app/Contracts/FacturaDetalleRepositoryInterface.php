<?php

namespace App\Contracts;

use App\Models\FacturaDetalle;
use Illuminate\Database\Eloquent\Collection;

interface FacturaDetalleRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?FacturaDetalle;
    
    public function create(array $data): FacturaDetalle;
    
    public function update(int $id, array $data): ?FacturaDetalle;
    
    public function delete(int $id): bool;
    
    public function getByFacturaId(int $facturaId): Collection;
    
    public function deleteByFacturaId(int $facturaId): bool;
}
