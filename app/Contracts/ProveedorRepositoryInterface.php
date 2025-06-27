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
    
    public function getProveedoresWithPagos(): Collection;
    
    public function findByEmail(string $email): ?Proveedor;
    
    public function getProveedoresConDeuda(): Collection;
    
    public function updateDeuda(int $id, float $nuevaDeuda): ?Proveedor;
    
    public function registrarPago(int $id, float $monto, string $descripcion = null): bool;
}
