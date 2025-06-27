<?php

namespace App\Services;

use App\Contracts\PagoProveedorRepositoryInterface;
use App\Models\PagoProveedor;
use Illuminate\Database\Eloquent\Collection;

class PagoProveedorService
{
    protected $pagoProveedorRepository;

    public function __construct(PagoProveedorRepositoryInterface $pagoProveedorRepository)
    {
        $this->pagoProveedorRepository = $pagoProveedorRepository;
    }

    public function getAllPagosProveedores(): Collection
    {
        return $this->pagoProveedorRepository->getAll();
    }

    public function findPagoProveedorById(int $id): ?PagoProveedor
    {
        return $this->pagoProveedorRepository->findById($id);
    }

    public function createPagoProveedor(array $data): array
    {
        // Validaciones de negocio
        if (!isset($data['proveedor_id']) || !isset($data['monto'])) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        if ($data['monto'] <= 0) {
            return ['success' => false, 'message' => 'El monto debe ser mayor a 0'];
        }

        return $this->pagoProveedorRepository->create($data);
    }

    public function updatePagoProveedor(int $id, array $data): ?PagoProveedor
    {
        return $this->pagoProveedorRepository->update($id, $data);
    }

    public function deletePagoProveedor(int $id): bool
    {
        return $this->pagoProveedorRepository->delete($id);
    }

    public function getPagosByProveedorId(int $proveedorId): Collection
    {
        return $this->pagoProveedorRepository->getByProveedorId($proveedorId);
    }

    public function getPagosByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->pagoProveedorRepository->getByFechaRange($fechaInicio, $fechaFin);
    }

    public function getTotalPagosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return $this->pagoProveedorRepository->getTotalPagosByPeriodo($fechaInicio, $fechaFin);
    }

    public function getPagosByMes(int $año, int $mes): Collection
    {
        return $this->pagoProveedorRepository->getPagosByMes($año, $mes);
    }

    public function getMetricas(): array
    {
        $hoy = now()->format('Y-m-d');
        $primerDiaMes = now()->startOfMonth()->format('Y-m-d');
        $ultimoDiaMes = now()->endOfMonth()->format('Y-m-d');

        return [
            'total_pagos_hoy' => $this->pagoProveedorRepository->getByFechaRange($hoy, $hoy)->sum('monto'),
            'total_pagos_mes' => $this->pagoProveedorRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->sum('monto'),
            'cantidad_pagos_mes' => $this->pagoProveedorRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->count(),
            'promedio_pago_mes' => $this->pagoProveedorRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->avg('monto'),
        ];
    }
}
