<?php

namespace App\Contracts;

use App\Models\Lavado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface LavadoRepositoryInterface
{
    /**
     * Obtener todos los lavados paginados
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Obtener todos los lavados
     */
    public function getAll(): Collection;

    /**
     * Obtener lavado por ID
     */
    public function findById(int $id): ?Lavado;

    /**
     * Crear nuevo lavado
     */
    public function create(array $data): array;

    /**
     * Actualizar lavado
     */
    public function update(int $id, array $data): Lavado;

    /**
     * Eliminar lavado
     */
    public function delete(int $id): bool;

    /**
     * Obtener lavados por cliente
     */
    public function getByCliente(int $clienteId): Collection;

    /**
     * Obtener lavados por empleado
     */
    public function getByEmpleado(int $empleadoId): Collection;

    /**
     * Obtener lavados por rango de fechas
     */
    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection;

    /**
     * Obtener estadísticas de lavados
     */
    public function getStats(): array;

    /**
     * Obtener lavados recientes
     */
    public function getRecientes(int $limit = 10): Collection;
}
