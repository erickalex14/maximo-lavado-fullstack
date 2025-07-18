<?php

namespace App\Contracts;

use App\Models\Venta;
use Illuminate\Database\Eloquent\Collection;

interface VentaRepositoryInterface
{
    /**
     * Obtener todas las ventas
     */
    public function getAll(): Collection;

    /**
     * Obtener todas incluyendo eliminadas (soft deletes)
     */
    public function getAllWithTrashed(): Collection;

    /**
     * Obtener solo las eliminadas (soft deletes)
     */
    public function getOnlyTrashed(): Collection;

    /**
     * Buscar venta por ID
     */
    public function findById(int $id): ?Venta;

    /**
     * Buscar venta por ID incluyendo eliminadas
     */
    public function findByIdWithTrashed(int $id): ?Venta;

    /**
     * Crear nueva venta con todo el flujo automático
     * (Venta + VentaDetalles + FacturaElectronica + Ingreso + Actualización Stock)
     */
    public function create(array $data): Venta;

    /**
     * Actualizar venta
     */
    public function update(int $id, array $data): bool;

    /**
     * Eliminar venta (soft delete)
     * Incluye reversión de stock y eliminación de factura/ingreso relacionados
     */
    public function delete(int $id): bool;

    /**
     * Restaurar venta eliminada
     */
    public function restore(int $id): bool;

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool;

    /**
     * Buscar ventas por cliente ID
     */
    public function findByClienteId(int $clienteId): Collection;

    /**
     * Buscar ventas por empleado ID
     */
    public function findByEmpleadoId(int $empleadoId): Collection;

    /**
     * Buscar ventas por vehículo ID
     */
    public function findByVehiculoId(int $vehiculoId): Collection;

    /**
     * Buscar ventas en rango de fechas
     */
    public function findByFechaRango(\DateTime $fechaInicio, \DateTime $fechaFin): Collection;

    /**
     * Obtener ventas con sus detalles
     */
    public function getVentasConDetalles(): Collection;

    /**
     * Calcular total de una venta
     */
    public function calcularTotal(int $ventaId): float;

    /**
     * Obtener métricas de ventas
     */
    public function getMetricas(array $filtros = []): array;

    /**
     * Validar stock disponible para una venta
     */
    public function validarStockDisponible(array $detalles): array;

    /**
     * Procesar venta completa con transacción atómica
     * (Validar stock + Crear venta + Generar factura + Crear ingreso + Actualizar inventario)
     */
    public function procesarVentaCompleta(array $data): array;

    /**
     * Obtener ventas del día
     */
    public function getVentasDelDia(\DateTime $fecha = null): Collection;

    /**
     * Obtener mejores clientes por volumen de ventas
     */
    public function getMejoresClientes(int $limite = 10): Collection;

    /**
     * Obtener productos/servicios más vendidos
     */
    public function getProductosMasVendidos(int $limite = 10): Collection;
}
