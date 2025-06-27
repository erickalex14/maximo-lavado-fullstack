<?php

namespace App\Contracts;

use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use Illuminate\Database\Eloquent\Collection;

interface VentaRepositoryInterface
{
    public function getVentasAutomotrices(): Collection;
    public function getVentasDespensa(): Collection;
    public function getAllVentas(): Collection;
    public function createVentaAutomotriz(array $data): VentaProductoAutomotriz;
    public function createVentaDespensa(array $data): VentaProductoDespensa;
    public function findVentaAutomotrizById(int $id): ?VentaProductoAutomotriz;
    public function findVentaDespensaById(int $id): ?VentaProductoDespensa;
    public function updateVentaAutomotriz(int $id, array $data): ?VentaProductoAutomotriz;
    public function updateVentaDespensa(int $id, array $data): ?VentaProductoDespensa;
    public function deleteVentaAutomotriz(int $id): bool;
    public function deleteVentaDespensa(int $id): bool;
    public function getVentasByClienteId(int $clienteId): Collection;
    public function getVentasByFechaRange(string $fechaInicio, string $fechaFin): Collection;
    public function getMetricas(): array;
}
