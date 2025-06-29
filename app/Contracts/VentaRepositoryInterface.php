<?php

namespace App\Contracts;

use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use Illuminate\Database\Eloquent\Collection;

interface VentaRepositoryInterface
{
    // Métodos generales
    public function getAllVentas(): Collection;
    public function getVentasByClienteId(int $clienteId): Collection;
    public function getVentasByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    public function getMetricas(): array;

    // Métodos específicos para ventas automotrices
    public function getAllVentasAutomotrices(): Collection;
    public function getVentasAutomotricesByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    public function createVentaAutomotriz(array $data): VentaProductoAutomotriz; // Crea venta + ingreso + factura
    public function findVentaAutomotrizById(int $id): ?VentaProductoAutomotriz;
    public function updateVentaAutomotriz(int $id, array $data): ?VentaProductoAutomotriz;
    public function deleteVentaAutomotriz(int $id): bool; // Elimina venta + ingreso + factura
    
    /**
     * Restaurar venta automotriz eliminada lógicamente (con restauración de ingresos y facturas)
     */
    public function restoreVentaAutomotriz(int $id): bool;
    
    /**
     * Obtener ventas automotrices eliminadas lógicamente
     */
    public function getTrashedVentasAutomotrices(): Collection;
    
    public function getMetricasAutomotrices(array $params = []): array;

    // Métodos específicos para ventas de despensa
    public function getAllVentasDespensa(): Collection;
    public function getVentasDespensaByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    public function createVentaDespensa(array $data): VentaProductoDespensa; // Crea venta + ingreso + factura
    public function findVentaDespensaById(int $id): ?VentaProductoDespensa;
    public function updateVentaDespensa(int $id, array $data): ?VentaProductoDespensa;
    public function deleteVentaDespensa(int $id): bool; // Elimina venta + ingreso + factura
    
    /**
     * Restaurar venta de despensa eliminada lógicamente (con restauración de ingresos y facturas)
     */
    public function restoreVentaDespensa(int $id): bool;
    
    /**
     * Obtener ventas de despensa eliminadas lógicamente
     */
    public function getTrashedVentasDespensa(): Collection;
    
    public function getMetricasDespensa(array $params = []): array;

    // Métodos adicionales
    public function getProductosDisponibles(): array;
    public function getClientes(): Collection;
    public function procesarVentaCompleta(array $data): array; // Procesa múltiples items + facturas automáticas

    // Métodos legacy (mantener compatibilidad)
    public function getVentasAutomotrices(): Collection;
    public function getVentasDespensa(): Collection;
}
