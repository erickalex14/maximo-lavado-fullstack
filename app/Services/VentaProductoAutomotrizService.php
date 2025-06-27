<?php

namespace App\Services;

use App\Contracts\VentaProductoAutomotrizRepositoryInterface;
use App\Models\VentaProductoAutomotriz;
use Illuminate\Database\Eloquent\Collection;

class VentaProductoAutomotrizService
{
    protected $ventaRepository;

    public function __construct(VentaProductoAutomotrizRepositoryInterface $ventaRepository)
    {
        $this->ventaRepository = $ventaRepository;
    }

    public function getAllVentas(): Collection
    {
        return $this->ventaRepository->getAll();
    }

    public function findVentaById(int $id): ?VentaProductoAutomotriz
    {
        return $this->ventaRepository->findById($id);
    }

    public function createVenta(array $data): array
    {
        // Validaciones de negocio
        if (!isset($data['producto_id']) || !isset($data['cantidad']) || !isset($data['precio_unitario'])) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        if ($data['cantidad'] <= 0) {
            return ['success' => false, 'message' => 'La cantidad debe ser mayor a 0'];
        }

        if ($data['precio_unitario'] <= 0) {
            return ['success' => false, 'message' => 'El precio unitario debe ser mayor a 0'];
        }

        return $this->ventaRepository->create($data);
    }

    public function updateVenta(int $id, array $data): ?VentaProductoAutomotriz
    {
        return $this->ventaRepository->update($id, $data);
    }

    public function deleteVenta(int $id): bool
    {
        return $this->ventaRepository->delete($id);
    }

    public function getVentasByProducto(int $productoId): Collection
    {
        return $this->ventaRepository->getByProductoId($productoId);
    }

    public function getVentasByCliente(int $clienteId): Collection
    {
        return $this->ventaRepository->getByClienteId($clienteId);
    }

    public function getVentasByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->ventaRepository->getByFechaRange($fechaInicio, $fechaFin);
    }

    public function getTotalVentasByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return $this->ventaRepository->getTotalVentasByPeriodo($fechaInicio, $fechaFin);
    }
}
