<?php

namespace App\Contracts;

use App\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;

interface ServicioRepositoryInterface
{
    /**
     * Obtener todos los servicios activos
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
     * Buscar servicio por ID
     */
    public function findById(int $id): ?Servicio;

    /**
     * Buscar servicio por ID incluyendo eliminados
     */
    public function findByIdWithTrashed(int $id): ?Servicio;

    /**
     * Crear nuevo servicio
     */
    public function create(array $data): Servicio;

    /**
     * Actualizar servicio
     */
    public function update(int $id, array $data): bool;

    /**
     * Eliminar servicio (soft delete)
     */
    public function delete(int $id): bool;

    /**
     * Restaurar servicio eliminado
     */
    public function restore(int $id): bool;

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool;

    /**
     * Buscar servicios por nombre
     */
    public function findByName(string $nombre): Collection;

    /**
     * Obtener servicios activos para ventas
     */
    public function getActivosParaVentas(): Collection;

    /**
     * Obtener servicios con precios por tipo de vehículo
     */
    public function getServiciosConPrecios(): Collection;

    /**
     * Buscar servicios por tipo de vehículo
     */
    public function findByTipoVehiculo(int $tipoVehiculoId): Collection;

    /**
     * Obtener precio de servicio para tipo de vehículo específico
     */
    public function getPrecioParaTipoVehiculo(int $servicioId, int $tipoVehiculoId): ?float;

    /**
     * Obtener servicios activos
     */
    public function getActiveServices(): Collection;

    /**
     * Obtener servicios inactivos
     */
    public function getInactiveServices(): Collection;

    /**
     * Buscar servicio por nombre
     */
    public function findByNombre(string $nombre): ?Servicio;

    /**
     * Verificar si existe un servicio con el nombre
     */
    public function existsByNombre(string $nombre, ?int $excludeId = null): bool;

    /**
     * Obtener servicios por tipo de vehículo
     */
    public function getByTipoVehiculo(int $tipoVehiculoId): Collection;

    /**
     * Actualizar precio de servicio para un tipo de vehículo
     */
    public function updatePrecio(int $servicioId, int $tipoVehiculoId, float $precio): bool;

    /**
     * Toggle estado activo del servicio
     */
    public function toggleActive(int $id): ?Servicio;

    /**
     * Obtener estadísticas de servicios
     */
    public function getStats(): array;

    /**
     * Buscar servicios por término
     */
    public function search(string $term): Collection;

    /**
     * Obtener servicios paginados
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
