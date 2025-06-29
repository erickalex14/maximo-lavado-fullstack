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
     * Obtener todos los lavados con filtros opcionales
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Obtener lavado por ID
     */
    public function findById(int $id): ?Lavado;

    /**
     * Crear nuevo lavado (con transacción, creación de ingreso y factura automática)
     */
    public function create(array $data): array;

    /**
     * Actualizar lavado (con transacción interna)
     */
    public function update(int $id, array $data): Lavado;

    /**
     * Eliminar lavado (con transacción interna, limpieza de ingresos y facturas)
     */
    public function delete(int $id): bool;

    /**
     * Restaurar lavado eliminado lógicamente (con restauración de ingresos y facturas)
     */
    public function restore(int $id): bool;

    /**
     * Obtener lavados eliminados lógicamente
     */
    public function getTrashed(): Collection;

    /**
     * Obtener lavados por cliente
     */
    public function getByCliente(int $clienteId): Collection;

    /**
     * Obtener lavados por empleado con filtros opcionales
     */
    public function getByEmpleado(int $empleadoId, array $filters = []): Collection;

    /**
     * Obtener lavados por vehículo con filtros opcionales
     */
    public function getByVehiculo(int $vehiculoId, array $filters = []): Collection;

    /**
     * Obtener lavados por día específico
     */
    public function getByDay(string $fecha, array $filters = []): Collection;

    /**
     * Obtener lavados por semana
     */
    public function getByWeek(string $fecha, array $filters = []): Collection;

    /**
     * Obtener lavados por mes
     */
    public function getByMonth(int $anio, int $mes, array $filters = []): Collection;

    /**
     * Obtener lavados por año
     */
    public function getByYear(int $anio, array $filters = []): Collection;

    /**
     * Obtener lavados por rango de fechas
     */
    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection;

    /**
     * Obtener estadísticas de lavados con filtros opcionales
     */
    public function getStats(array $filters = []): array;

    /**
     * Obtener lavados recientes
     */
    public function getRecientes(int $limit = 10): Collection;
}
