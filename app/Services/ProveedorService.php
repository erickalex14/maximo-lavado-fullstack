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

    public function registrarPago(int $id, float $monto, string $descripcion = null): array
    {
        if ($monto <= 0) {
            return ['success' => false, 'message' => 'El monto debe ser mayor a 0'];
        }

        $proveedor = $this->findProveedorById($id);
        if (!$proveedor) {
            return ['success' => false, 'message' => 'Proveedor no encontrado'];
        }

        if ($proveedor->deuda_pendiente < $monto) {
            return ['success' => false, 'message' => 'No se puede pagar más de lo que se debe'];
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

    // =======================================================
    // MÉTODOS CONSOLIDADOS PARA GESTIÓN COMPLETA DE PAGOS
    // =======================================================

    /**
     * Obtener todos los pagos de todos los proveedores
     */
    public function getAllPagos(): Collection
    {
        return $this->proveedorRepository->getAllPagos();
    }

    /**
     * Obtener un pago específico por ID
     */
    public function getPagoById(int $pagoId): ?object
    {
        return $this->proveedorRepository->getPagoById($pagoId);
    }

    /**
     * Crear un pago con transacción completa:
     * 1. Registra el pago en pagos_proveedores
     * 2. Actualiza deuda_pendiente del proveedor
     * 3. Registra egreso en egresos
     */
    public function createPago(array $data): array
    {
        // Validaciones de negocio
        if (!isset($data['proveedor_id']) || !isset($data['monto'])) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        if ($data['monto'] <= 0) {
            return ['success' => false, 'message' => 'El monto debe ser mayor a 0'];
        }

        $proveedor = $this->findProveedorById($data['proveedor_id']);
        if (!$proveedor) {
            return ['success' => false, 'message' => 'Proveedor no encontrado'];
        }

        if ($proveedor->deuda_pendiente < $data['monto']) {
            return ['success' => false, 'message' => 'No se puede pagar más de lo que se debe'];
        }

        // Ejecutar transacción completa en el repositorio
        return $this->proveedorRepository->createPago($data);
    }

    /**
     * Actualizar un pago específico
     */
    public function updatePago(int $pagoId, array $data): ?object
    {
        return $this->proveedorRepository->updatePago($pagoId, $data);
    }

    /**
     * Eliminar un pago específico
     */
    public function deletePago(int $pagoId): bool
    {
        return $this->proveedorRepository->deletePago($pagoId);
    }

    /**
     * Obtener pagos por rango de fechas
     */
    public function getPagosByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->proveedorRepository->getPagosByFechaRange($fechaInicio, $fechaFin);
    }

    /**
     * Obtener métricas de pagos
     */
    public function getMetricasPagos(array $params = []): array
    {
        return $this->proveedorRepository->getMetricasPagos($params);
    }
}
