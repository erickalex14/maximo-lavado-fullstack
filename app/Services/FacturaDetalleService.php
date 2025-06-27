<?php

namespace App\Services;

use App\Contracts\FacturaDetalleRepositoryInterface;
use App\Models\FacturaDetalle;
use Illuminate\Database\Eloquent\Collection;

class FacturaDetalleService
{
    protected $facturaDetalleRepository;

    public function __construct(FacturaDetalleRepositoryInterface $facturaDetalleRepository)
    {
        $this->facturaDetalleRepository = $facturaDetalleRepository;
    }

    public function getAllFacturaDetalles(): Collection
    {
        return $this->facturaDetalleRepository->getAll();
    }

    public function findFacturaDetalleById(int $id): ?FacturaDetalle
    {
        return $this->facturaDetalleRepository->findById($id);
    }

    public function createFacturaDetalle(array $data): FacturaDetalle
    {
        return $this->facturaDetalleRepository->create($data);
    }

    public function updateFacturaDetalle(int $id, array $data): ?FacturaDetalle
    {
        return $this->facturaDetalleRepository->update($id, $data);
    }

    public function deleteFacturaDetalle(int $id): bool
    {
        return $this->facturaDetalleRepository->delete($id);
    }

    public function getDetallesByFacturaId(int $facturaId): Collection
    {
        return $this->facturaDetalleRepository->getByFacturaId($facturaId);
    }

    public function deleteDetallesByFacturaId(int $facturaId): bool
    {
        return $this->facturaDetalleRepository->deleteByFacturaId($facturaId);
    }

    public function createMultipleDetalles(int $facturaId, array $detalles): Collection
    {
        $createdDetalles = collect();
        
        foreach ($detalles as $detalle) {
            $detalle['factura_id'] = $facturaId;
            $createdDetalles->push($this->createFacturaDetalle($detalle));
        }
        
        return $createdDetalles;
    }
}
