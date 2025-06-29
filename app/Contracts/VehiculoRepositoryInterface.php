<?php

namespace App\Contracts;

use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface VehiculoRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function getAll(): Collection;
    public function findById(int $id): ?Vehiculo;
    public function create(array $data): Vehiculo;
    public function update(int $id, array $data): Vehiculo;
    public function delete(int $id): bool;
    
    /**
     * Restaurar vehículo eliminado lógicamente
     */
    public function restore(int $id): bool;
    
    /**
     * Obtener vehículos eliminados lógicamente
     */
    public function getTrashed(): Collection;
    
    public function getByCliente(int $clienteId): Collection;
    public function existsByPlaca(string $placa, ?int $excludeId = null): bool;
    public function getStats(): array;
}
