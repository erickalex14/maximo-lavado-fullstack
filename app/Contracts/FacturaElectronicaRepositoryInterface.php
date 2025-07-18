<?php

namespace App\Contracts;

use App\Models\FacturaElectronica;
use Illuminate\Database\Eloquent\Collection;

interface FacturaElectronicaRepositoryInterface
{
    /**
     * Obtener todas las facturas electrónicas
     */
    public function getAll(): Collection;

    /**
     * Obtener todas incluyendo eliminadas (soft deletes)
     */
    public function getAllWithTrashed(): Collection;

    /**
     * Obtener solo las eliminadas (soft deletes)
     */
    public function getOnlyTrashed(): Collection;

    /**
     * Buscar factura electrónica por ID
     */
    public function findById(int $id): ?FacturaElectronica;

    /**
     * Buscar factura electrónica por ID incluyendo eliminadas
     */
    public function findByIdWithTrashed(int $id): ?FacturaElectronica;

    /**
     * Crear nueva factura electrónica
     */
    public function create(array $data): FacturaElectronica;

    /**
     * Actualizar factura electrónica
     */
    public function update(int $id, array $data): bool;

    /**
     * Eliminar factura electrónica (soft delete)
     */
    public function delete(int $id): bool;

    /**
     * Restaurar factura electrónica eliminada
     */
    public function restore(int $id): bool;

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool;

    /**
     * Buscar por venta ID
     */
    public function findByVentaId(int $ventaId): ?FacturaElectronica;

    /**
     * Buscar por secuencial único SRI
     */
    public function findBySecuencial(string $establecimiento, string $puntoEmision, int $secuencial): ?FacturaElectronica;

    /**
     * Obtener próximo secuencial disponible
     */
    public function getProximoSecuencial(string $establecimiento = '001', string $puntoEmision = '001'): int;

    /**
     * Buscar por RUC emisor
     */
    public function findByRucEmisor(string $rucEmisor): Collection;

    /**
     * Buscar por identificación comprador
     */
    public function findByIdentificacionComprador(string $identificacion): Collection;

    /**
     * Obtener facturas por estado SRI
     */
    public function findByEstadoSri(string $estado): Collection;

    /**
     * Obtener facturas en rango de fechas
     */
    public function findByFechaRango(\DateTime $fechaInicio, \DateTime $fechaFin): Collection;

    /**
     * Actualizar estado y respuesta SRI
     */
    public function actualizarRespuestaSri(int $id, array $dataSri): bool;

    /**
     * Obtener facturas pendientes de autorización SRI
     */
    public function getPendientesAutorizacion(): Collection;

    /**
     * Generar XML para SRI
     */
    public function generarXmlSri(int $id): ?string;

    /**
     * Marcar como autorizada por SRI
     */
    public function marcarComoAutorizada(int $id, string $xmlAutorizado, \DateTime $fechaAutorizacion): bool;
}
