<?php

namespace App\Contracts;

use App\Models\TipoVehiculo;
use Illuminate\Database\Eloquent\Collection;

interface TipoVehiculoRepositoryInterface
{
    /**
     * Obtener todos los tipos de vehículo activos
     */
    public function getAll(): Collection;

    /**
     * Obtener todos incluyendo eliminados (soft deletes)
     */
    public function getAllWithTrashed(): Collection;

    /**
     * Obtener solo los eliminados (soft deletes)
     */
    public function getOnlyTrashed(): Collection;

    /**
     * Buscar tipo de vehículo por ID
     */
    public function findById(int $id): ?TipoVehiculo;

    /**
     * Buscar tipo de vehículo por ID incluyendo eliminados
     */
    public function findByIdWithTrashed(int $id): ?TipoVehiculo;

    /**
     * Crear nuevo tipo de vehículo
     */
    public function create(array $data): TipoVehiculo;

    /**
     * Actualizar tipo de vehículo
     */
    public function update(int $id, array $data): bool;

    /**
     * Eliminar tipo de vehículo (soft delete)
     */
    public function delete(int $id): bool;

    /**
     * Restaurar tipo de vehículo eliminado
     */
    public function restore(int $id): bool;

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool;

    /**
     * Buscar tipos de vehículo por nombre
     */
    public function findByName(string $nombre): Collection;

    /**
     * Obtener tipos de vehículo activos para servicios
     */
    public function getActivosParaServicios(): Collection;
}
