<?php

namespace App\Contracts;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Collection;

interface ProveedorRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?Proveedor;
    
    public function create(array $data): Proveedor;
    
    public function update(int $id, array $data): ?Proveedor;
    
    public function delete(int $id): bool;
    
    /**
     * Restaurar proveedor eliminado lógicamente
     */
    public function restore(int $id): bool;
    
    /**
     * Obtener proveedores eliminados lógicamente
     */
    public function getTrashed(): Collection;
    
    public function getProveedoresWithPagos(): Collection;
    
    public function findByEmail(string $email): ?Proveedor;
    
    public function getProveedoresConDeuda(): Collection;
    
    public function updateDeuda(int $id, float $nuevaDeuda): ?Proveedor;
    
    public function registrarPago(int $id, float $monto, string $descripcion = null): array;
    
    // Métodos consolidados para gestión de pagos
    public function getAllPagos(): Collection;
    
    public function getPagoById(int $pagoId): ?object;
    
    public function createPago(array $data): array;
    
    public function updatePago(int $pagoId, array $data): ?object;
    
    public function deletePago(int $pagoId): bool;
    
    public function getPagosByProveedorId(int $proveedorId): Collection;
    
    public function getPagosByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    
    public function getMetricasPagos(array $params = []): array;
}
