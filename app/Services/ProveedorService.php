<?php

namespace App\Services;

use App\Contracts\ProveedorRepositoryInterface;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Collection;

class ProveedorService
{
    protected $proveedorRepository;

    public function __construct(ProveedorRepositoryInterface $proveedorRepository)
    {
        $this->proveedorRepository = $proveedorRepository;
    }

    public function getAllProveedores(): Collection
    {
        return $this->proveedorRepository->getAll();
    }

    public function getProveedoresWithPagos(): Collection
    {
        return $this->proveedorRepository->getProveedoresWithPagos();
    }

    public function findProveedorById(int $id): ?Proveedor
    {
        return $this->proveedorRepository->findById($id);
    }

    public function findProveedorByEmail(string $email): ?Proveedor
    {
        return $this->proveedorRepository->findByEmail($email);
    }

    public function createProveedor(array $data): Proveedor
    {
        return $this->proveedorRepository->create($data);
    }

    public function updateProveedor(int $id, array $data): ?Proveedor
    {
        return $this->proveedorRepository->update($id, $data);
    }

    public function deleteProveedor(int $id): bool
    {
        return $this->proveedorRepository->delete($id);
    }

    public function getProveedoresConDeuda(): Collection
    {
        return $this->proveedorRepository->getProveedoresConDeuda();
    }

    public function getDeudaProveedor(int $id): ?float
    {
        $proveedor = $this->findProveedorById($id);
        return $proveedor ? $proveedor->deuda_pendiente : null;
    }

    public function updateDeuda(int $id, float $nuevaDeuda): ?Proveedor
    {
        return $this->proveedorRepository->updateDeuda($id, $nuevaDeuda);
    }

    public function registrarPago(int $id, float $monto, string $descripcion = null): bool
    {
        if ($monto <= 0) {
            return false;
        }

        $proveedor = $this->findProveedorById($id);
        if (!$proveedor) {
            return false;
        }

        if ($proveedor->deuda_pendiente < $monto) {
            return false; // No se puede pagar más de lo que se debe
        }

        return $this->proveedorRepository->registrarPago($id, $monto, $descripcion);
    }

    public function getPagosProveedor(int $id): Collection
    {
        $proveedor = $this->findProveedorById($id);
        return $proveedor ? $proveedor->pagos : collect();
    }

    public function agregarDeuda(int $id, float $montoDeuda, string $descripcion = null): ?Proveedor
    {
        if ($montoDeuda <= 0) {
            return null;
        }

        $proveedor = $this->findProveedorById($id);
        if ($proveedor) {
            $nuevaDeuda = $proveedor->deuda_pendiente + $montoDeuda;
            return $this->updateDeuda($id, $nuevaDeuda);
        }
        return null;
    }

    public function getTotalDeudas(): float
    {
        return $this->proveedorRepository->getAll()->sum('deuda_pendiente');
    }
}
