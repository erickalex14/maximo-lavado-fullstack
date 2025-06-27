<?php

namespace App\Contracts;

use App\Models\Factura;
use Illuminate\Database\Eloquent\Collection;

interface FacturaRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?Factura;
    
    public function create(array $data): Factura;
    
    public function update(int $id, array $data): ?Factura;
    
    public function delete(int $id): bool;
    
    public function getByClienteId(int $clienteId): Collection;
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    
    public function getTotalFacturasByPeriodo(string $fechaInicio, string $fechaFin): float;
    
    public function getFacturasByMes(int $año, int $mes): Collection;
    
    public function findByNumeroFactura(string $numeroFactura): ?Factura;
}
