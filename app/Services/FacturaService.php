<?php

namespace App\Services;

use App\Contracts\FacturaRepositoryInterface;
use App\Models\Factura;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class FacturaService
{
    public function __construct(
        protected FacturaRepositoryInterface $facturaRepository
    ) {}

    /**
     * Obtener todas las facturas
     */
    public function getAllFacturas(): Collection
    {
        return $this->facturaRepository->getAll();
    }

    /**
     * Obtener factura por ID
     */
    public function getFacturaById(int $id): ?Factura
    {
        return $this->facturaRepository->findById($id);
    }

    /**
     * Crear nueva factura
     */
    public function createFactura(array $data): Factura
    {
        // Validaciones de negocio
        $this->validateFacturaData($data);

        $factura = $this->facturaRepository->create($data);

        Log::info('Factura creada exitosamente', [
            'factura_id' => $factura->factura_id,
            'numero_factura' => $factura->numero_factura
        ]);

        return $factura;
    }

    /**
     * Actualizar factura
     */
    public function updateFactura(int $id, array $data): ?Factura
    {
        // Verificar que la factura existe
        $factura = $this->facturaRepository->findById($id);
        if (!$factura) {
            throw new \Exception('Factura no encontrada');
        }

        // Validaciones de negocio
        $this->validateFacturaData($data, $id);

        $facturaActualizada = $this->facturaRepository->update($id, $data);

        Log::info('Factura actualizada exitosamente', [
            'factura_id' => $id
        ]);

        return $facturaActualizada;
    }

    /**
     * Eliminar factura
     */
    public function deleteFactura(int $id): bool
    {
        $factura = $this->facturaRepository->findById($id);
        if (!$factura) {
            throw new \Exception('Factura no encontrada');
        }

        $result = $this->facturaRepository->delete($id);

        Log::info('Factura eliminada exitosamente', [
            'factura_id' => $id
        ]);

        return $result;
    }

    /**
     * Restaurar factura
     */
    public function restoreFactura(int $id): bool
    {
        return $this->facturaRepository->restore($id);
    }

    /**
     * Obtener facturas eliminadas
     */
    public function getTrashedFacturas(): Collection
    {
        return $this->facturaRepository->getTrashed();
    }

    /**
     * Obtener métricas de facturas
     */
    public function getMetricas(): array
    {
        return $this->facturaRepository->getMetricas();
    }

    /**
     * Validaciones de reglas de negocio
     */
    protected function validateFacturaData(array $data, ?int $excludeId = null): void
    {
        // Verificar que el cliente existe
        if (isset($data['cliente_id'])) {
            $cliente = \App\Models\Cliente::find($data['cliente_id']);
            if (!$cliente) {
                throw new \Exception('El cliente seleccionado no existe');
            }
        }

        // Validar que hay detalles
        if (isset($data['detalles']) && empty($data['detalles'])) {
            throw new \Exception('La factura debe tener al menos un detalle');
        }

        // Validar detalles si están presentes
        if (isset($data['detalles'])) {
            foreach ($data['detalles'] as $detalle) {
                if (empty($detalle['descripcion'])) {
                    throw new \Exception('Todos los detalles deben tener descripción');
                }
                
                if (!isset($detalle['cantidad']) || $detalle['cantidad'] <= 0) {
                    throw new \Exception('La cantidad debe ser mayor a 0');
                }
                
                if (!isset($detalle['precio_unitario']) || $detalle['precio_unitario'] <= 0) {
                    throw new \Exception('El precio unitario debe ser mayor a 0');
                }
            }
        }
    }
}
