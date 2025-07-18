<?php

namespace App\Contracts;

use App\Models\VentaDetalle;
use Illuminate\Database\Eloquent\Collection;

interface VentaDetalleRepositoryInterface
{
    /**
     * Obtener todos los detalles de venta
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
     * Buscar detalle de venta por ID
     */
    public function findById(int $id): ?VentaDetalle;

    /**
     * Buscar detalle de venta por ID incluyendo eliminados
     */
    public function findByIdWithTrashed(int $id): ?VentaDetalle;

    /**
     * Crear nuevo detalle de venta
     */
    public function create(array $data): VentaDetalle;

    /**
     * Actualizar detalle de venta
     */
    public function update(int $id, array $data): bool;

    /**
     * Eliminar detalle de venta (soft delete)
     */
    public function delete(int $id): bool;

    /**
     * Restaurar detalle de venta eliminado
     */
    public function restore(int $id): bool;

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool;

    /**
     * Obtener detalles por venta ID
     */
    public function findByVentaId(int $ventaId): Collection;

    /**
     * Obtener detalles por producto (polimórfico)
     */
    public function findByProducto(string $productoType, int $productoId): Collection;

    /**
     * Crear múltiples detalles para una venta
     */
    public function createBatch(array $detalles): Collection;

    /**
     * Calcular subtotal de una venta
     */
    public function calcularSubtotalVenta(int $ventaId): float;

    /**
     * Obtener detalles de productos de una venta
     */
    public function getProductosDeVenta(int $ventaId): Collection;

    /**
     * Obtener detalles de servicios de una venta
     */
    public function getServiciosDeVenta(int $ventaId): Collection;
}
