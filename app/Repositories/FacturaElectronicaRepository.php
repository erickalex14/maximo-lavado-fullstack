<?php

namespace App\Repositories;

use App\Contracts\FacturaElectronicaRepositoryInterface;
use App\Models\FacturaElectronica;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FacturaElectronicaRepository implements FacturaElectronicaRepositoryInterface
{
    /**
     * Obtener todas las facturas electrónicas
     */
    public function getAll(): Collection
    {
        return FacturaElectronica::all();
    }

    /**
     * Obtener todas incluyendo eliminadas (soft deletes)
     */
    public function getAllWithTrashed(): Collection
    {
        return FacturaElectronica::withTrashed()->get();
    }

    /**
     * Obtener solo las eliminadas (soft deletes)
     */
    public function getOnlyTrashed(): Collection
    {
        return FacturaElectronica::onlyTrashed()->get();
    }

    /**
     * Buscar factura electrónica por ID
     */
    public function findById(int $id): ?FacturaElectronica
    {
        return FacturaElectronica::find($id);
    }

    /**
     * Buscar factura electrónica por ID incluyendo eliminadas
     */
    public function findByIdWithTrashed(int $id): ?FacturaElectronica
    {
        return FacturaElectronica::withTrashed()->find($id);
    }

    /**
     * Crear nueva factura electrónica
     */
    public function create(array $data): FacturaElectronica
    {
        return DB::transaction(function () use ($data) {
            return FacturaElectronica::create($data);
        });
    }

    /**
     * Actualizar factura electrónica
     */
    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $factura = FacturaElectronica::find($id);
            if ($factura) {
                return $factura->update($data);
            }
            return false;
        });
    }

    /**
     * Eliminar factura electrónica (soft delete)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $factura = FacturaElectronica::find($id);
            return $factura ? $factura->delete() : false;
        });
    }

    /**
     * Restaurar factura electrónica eliminada
     */
    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $factura = FacturaElectronica::onlyTrashed()->find($id);
            return $factura ? $factura->restore() : false;
        });
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $factura = FacturaElectronica::withTrashed()->find($id);
            return $factura ? $factura->forceDelete() : false;
        });
    }

    /**
     * Buscar por venta ID
     */
    public function findByVentaId(int $ventaId): ?FacturaElectronica
    {
        return FacturaElectronica::where('venta_id', $ventaId)->first();
    }

    /**
     * Buscar por secuencial
     */
    public function findBySecuencial(string $establecimiento, string $puntoEmision, int $secuencial): ?FacturaElectronica
    {
        return FacturaElectronica::where('establecimiento', $establecimiento)
                                ->where('punto_emision', $puntoEmision)
                                ->where('secuencial', $secuencial)
                                ->first();
    }

    /**
     * Generar siguiente secuencial
     */
    public function getNextSecuencial(string $establecimiento = '001', string $puntoEmision = '001'): int
    {
        $ultimo = FacturaElectronica::where('establecimiento', $establecimiento)
                                  ->where('punto_emision', $puntoEmision)
                                  ->max('secuencial');
        
        return ($ultimo ?? 0) + 1;
    }

    /**
     * Obtener próximo secuencial disponible (alias para compatibilidad con interface)
     */
    public function getProximoSecuencial(string $establecimiento = '001', string $puntoEmision = '001'): int
    {
        return $this->getNextSecuencial($establecimiento, $puntoEmision);
    }

    /**
     * Buscar por RUC emisor
     */
    public function findByRucEmisor(string $rucEmisor): Collection
    {
        return FacturaElectronica::where('ruc_emisor', $rucEmisor)->get();
    }

    /**
     * Buscar por identificación comprador
     */
    public function findByIdentificacionComprador(string $identificacion): Collection
    {
        return FacturaElectronica::where('identificacion_comprador', $identificacion)->get();
    }

    /**
     * Obtener facturas por rango de fechas
     */
    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return FacturaElectronica::whereHas('venta', function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        })->with('venta')->get();
    }

    /**
     * Obtener facturas por estado SRI
     */
    public function getByEstadoSri(string $estado): Collection
    {
        return FacturaElectronica::where('estado_sri', $estado)->get();
    }

    /**
     * Buscar por estado SRI (alias para compatibilidad con interface)
     */
    public function findByEstadoSri(string $estado): Collection
    {
        return $this->getByEstadoSri($estado);
    }

    /**
     * Obtener facturas en rango de fechas (compatible con interface)
     */
    public function findByFechaRango(\DateTime $fechaInicio, \DateTime $fechaFin): Collection
    {
        return $this->getByDateRange($fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d'));
    }

    /**
     * Obtener facturas autorizadas
     */
    public function getAutorizadas(): Collection
    {
        return FacturaElectronica::where('estado_sri', 'AUTORIZADA')->get();
    }

    /**
     * Obtener facturas rechazadas
     */
    public function getRechazadas(): Collection
    {
        return FacturaElectronica::where('estado_sri', 'RECHAZADA')->get();
    }

    /**
     * Obtener facturas pendientes
     */
    public function getPendientes(): Collection
    {
        return FacturaElectronica::where('estado_sri', 'BORRADOR')->get();
    }

    /**
     * Actualizar estado SRI
     */
    public function updateEstadoSri(int $id, string $estado, ?string $mensaje = null, ?array $errores = null): ?FacturaElectronica
    {
        return DB::transaction(function () use ($id, $estado, $mensaje, $errores) {
            $factura = FacturaElectronica::find($id);
            if ($factura) {
                $updateData = ['estado_sri' => $estado];
                
                if ($mensaje) {
                    $updateData['mensaje_sri'] = $mensaje;
                }
                
                if ($errores) {
                    $updateData['errores_sri'] = $errores;
                }
                
                if ($estado === 'AUTORIZADA') {
                    $updateData['fecha_autorizacion'] = now();
                }
                
                $factura->update($updateData);
                return $factura->fresh();
            }
            return null;
        });
    }

    /**
     * Actualizar XML autorizado
     */
    public function updateXmlAutorizado(int $id, string $xmlAutorizado, ?string $claveAcceso = null): ?FacturaElectronica
    {
        return DB::transaction(function () use ($id, $xmlAutorizado, $claveAcceso) {
            $factura = FacturaElectronica::find($id);
            if ($factura) {
                $updateData = ['xml_autorizado' => $xmlAutorizado];
                
                if ($claveAcceso) {
                    $updateData['clave_acceso'] = $claveAcceso;
                }
                
                $factura->update($updateData);
                return $factura->fresh();
            }
            return null;
        });
    }

    /**
     * Obtener total de ventas por rango de fechas
     */
    public function getTotalVentasByDateRange(string $fechaInicio, string $fechaFin): float
    {
        return FacturaElectronica::where('estado_sri', 'AUTORIZADA')
                                ->whereHas('venta', function ($query) use ($fechaInicio, $fechaFin) {
                                    $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                                })
                                ->with('venta')
                                ->get()
                                ->sum('venta.total');
    }

    /**
     * Obtener estadísticas de facturas electrónicas
     */
    public function getStats(): array
    {
        return [
            'total' => FacturaElectronica::count(),
            'autorizadas' => FacturaElectronica::where('estado_sri', 'AUTORIZADA')->count(),
            'rechazadas' => FacturaElectronica::where('estado_sri', 'RECHAZADA')->count(),
            'pendientes' => FacturaElectronica::where('estado_sri', 'BORRADOR')->count(),
            'eliminadas' => FacturaElectronica::onlyTrashed()->count(),
        ];
    }

    /**
     * Buscar facturas por término (RUC, razón social, etc.)
     */
    public function search(string $term): Collection
    {
        return FacturaElectronica::where(function ($query) use ($term) {
            $query->where('ruc_emisor', 'ILIKE', "%{$term}%")
                  ->orWhere('razon_social_emisor', 'ILIKE', "%{$term}%")
                  ->orWhere('identificacion_comprador', 'ILIKE', "%{$term}%")
                  ->orWhere('razon_social_comprador', 'ILIKE', "%{$term}%")
                  ->orWhere('secuencial', 'ILIKE', "%{$term}%");
        })->with('venta')->get();
    }

    /**
     * Validar que no exista una factura con el mismo secuencial
     */
    public function existsBySecuencial(string $establecimiento, string $puntoEmision, int $secuencial, ?int $excludeId = null): bool
    {
        $query = FacturaElectronica::where('establecimiento', $establecimiento)
                                  ->where('punto_emision', $puntoEmision)
                                  ->where('secuencial', $secuencial);
        
        if ($excludeId) {
            $query->where('factura_electronica_id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Actualizar estado y respuesta SRI
     */
    public function actualizarRespuestaSri(int $id, array $dataSri): bool
    {
        return DB::transaction(function () use ($id, $dataSri) {
            $factura = FacturaElectronica::find($id);
            if ($factura) {
                return $factura->update($dataSri);
            }
            return false;
        });
    }

    /**
     * Obtener facturas pendientes de autorización SRI
     */
    public function getPendientesAutorizacion(): Collection
    {
        return FacturaElectronica::whereIn('estado_sri', ['BORRADOR', 'RECIBIDA', 'DEVUELTA'])->get();
    }

    /**
     * Generar XML para SRI
     */
    public function generarXmlSri(int $id): ?string
    {
        $factura = FacturaElectronica::find($id);
        if ($factura && $factura->xml_generado) {
            return $factura->xml_generado;
        }
        return null;
    }

    /**
     * Marcar como autorizada por SRI
     */
    public function marcarComoAutorizada(int $id, string $xmlAutorizado, \DateTime $fechaAutorizacion): bool
    {
        return DB::transaction(function () use ($id, $xmlAutorizado, $fechaAutorizacion) {
            $factura = FacturaElectronica::find($id);
            if ($factura) {
                return $factura->update([
                    'estado_sri' => 'AUTORIZADA',
                    'xml_autorizado' => $xmlAutorizado,
                    'fecha_autorizacion' => $fechaAutorizacion,
                ]);
            }
            return false;
        });
    }
}
