<?php

namespace App\Services;

use App\Contracts\FacturaRepositoryInterface;
use App\Contracts\FacturaDetalleRepositoryInterface;
use App\Models\Factura;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FacturaService
{
    protected $facturaRepository;
    protected $facturaDetalleRepository;

    public function __construct(
        FacturaRepositoryInterface $facturaRepository,
        FacturaDetalleRepositoryInterface $facturaDetalleRepository
    ) {
        $this->facturaRepository = $facturaRepository;
        $this->facturaDetalleRepository = $facturaDetalleRepository;
    }

    public function getAllFacturas(): Collection
    {
        return $this->facturaRepository->getAll();
    }

    public function findFacturaById(int $id): ?Factura
    {
        return $this->facturaRepository->findById($id);
    }

    public function createFactura(array $data): Factura
    {
        return DB::transaction(function () use ($data) {
            // Extraer detalles del array principal
            $detalles = $data['detalles'] ?? [];
            unset($data['detalles']);
            
            // Generar número de factura automáticamente si no se proporciona
            if (!isset($data['numero_factura'])) {
                $data['numero_factura'] = $this->generateNumeroFactura();
            }
            
            // Crear la factura
            $factura = $this->facturaRepository->create($data);
            
            // Crear los detalles de la factura
            if (!empty($detalles)) {
                foreach ($detalles as $detalle) {
                    $detalle['factura_id'] = $factura->factura_id;
                    $this->facturaDetalleRepository->create($detalle);
                }
            }
            
            return $factura->load('detalles');
        });
    }

    public function updateFactura(int $id, array $data): ?Factura
    {
        return DB::transaction(function () use ($id, $data) {
            $factura = $this->facturaRepository->findById($id);
            if (!$factura) {
                return null;
            }
            
            // Extraer detalles del array principal
            $detalles = $data['detalles'] ?? null;
            unset($data['detalles']);
            
            // Actualizar la factura
            $facturaActualizada = $this->facturaRepository->update($id, $data);
            
            // Si se proporcionan detalles, actualizarlos
            if ($detalles !== null) {
                // Eliminar detalles existentes
                $this->facturaDetalleRepository->deleteByFacturaId($id);
                
                // Crear nuevos detalles
                foreach ($detalles as $detalle) {
                    $detalle['factura_id'] = $id;
                    unset($detalle['factura_detalle_id']); // Remover ID si existe para crear nuevo
                    $this->facturaDetalleRepository->create($detalle);
                }
            }
            
            return $facturaActualizada ? $facturaActualizada->load('detalles') : null;
        });
    }

    public function deleteFactura(int $id): bool
    {
        return $this->facturaRepository->delete($id);
    }

    public function getFacturasByClienteId(int $clienteId): Collection
    {
        return $this->facturaRepository->getByClienteId($clienteId);
    }

    public function getFacturasByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->facturaRepository->getByFechaRange($fechaInicio, $fechaFin);
    }

    public function getTotalFacturasByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return $this->facturaRepository->getTotalFacturasByPeriodo($fechaInicio, $fechaFin);
    }

    public function getFacturasByMes(int $año, int $mes): Collection
    {
        return $this->facturaRepository->getFacturasByMes($año, $mes);
    }

    public function findByNumeroFactura(string $numeroFactura): ?Factura
    {
        return $this->facturaRepository->findByNumeroFactura($numeroFactura);
    }

    public function getMetricas(): array
    {
        $hoy = now()->format('Y-m-d');
        $primerDiaMes = now()->startOfMonth()->format('Y-m-d');
        $ultimoDiaMes = now()->endOfMonth()->format('Y-m-d');

        return [
            'total_facturas_hoy' => $this->facturaRepository->getByFechaRange($hoy, $hoy)->sum('total'),
            'total_facturas_mes' => $this->facturaRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->sum('total'),
            'cantidad_facturas_mes' => $this->facturaRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->count(),
            'promedio_factura_mes' => $this->facturaRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->avg('total'),
        ];
    }

    /**
     * Generar número de factura único
     */
    private function generateNumeroFactura(): string
    {
        $prefix = 'FAC-';
        $year = now()->year;
        $month = now()->format('m');
        
        // Buscar el último número de factura del mes actual
        $lastFactura = Factura::where('numero_factura', 'like', $prefix . $year . $month . '%')
            ->orderBy('numero_factura', 'desc')
            ->first();
        
        if ($lastFactura) {
            // Extraer el número secuencial del último número de factura
            $lastNumber = (int) substr($lastFactura->numero_factura, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
