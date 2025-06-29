<?php

namespace App\Repositories;

use App\Contracts\FacturaRepositoryInterface;
use App\Contracts\FacturaDetalleRepositoryInterface;
use App\Models\Factura;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FacturaRepository implements FacturaRepositoryInterface
{
    protected $facturaDetalleRepository;

    public function __construct(FacturaDetalleRepositoryInterface $facturaDetalleRepository)
    {
        $this->facturaDetalleRepository = $facturaDetalleRepository;
    }

    public function getAll(): Collection
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findById(int $id): ?Factura
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->find($id);
    }
    
    public function create(array $data): Factura
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
            $factura = Factura::create($data);
            
            // Crear los detalles de la factura
            if (!empty($detalles)) {
                foreach ($detalles as $detalle) {
                    $detalle['factura_id'] = $factura->factura_id;
                    $this->facturaDetalleRepository->create($detalle);
                }
            }
            
            return $factura->load('facturaDetalles');
        });
    }
    
    public function update(int $id, array $data): ?Factura
    {
        return DB::transaction(function () use ($id, $data) {
            $factura = Factura::find($id);
            
            if (!$factura) {
                return null;
            }
            
            // Extraer detalles del array principal
            $detalles = $data['detalles'] ?? null;
            unset($data['detalles']);
            
            // Actualizar la factura
            $factura->update($data);
            
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
            
            return $factura->fresh(['cliente', 'facturaDetalles']);
        });
    }
    
    public function delete(int $id): bool
    {
        $factura = Factura::find($id);
        
        if (!$factura) {
            return false;
        }
        
        return $factura->delete();
    }
    
    public function restore(int $id): bool
    {
        $factura = Factura::onlyTrashed()->find($id);
        if ($factura) {
            return $factura->restore();
        }
        return false;
    }
    
    public function getTrashed(): Collection
    {
        return Factura::onlyTrashed()
            ->with(['cliente'])
            ->orderBy('deleted_at', 'desc')
            ->get();
    }
    
    public function getByClienteId(int $clienteId): Collection
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->where('cliente_id', $clienteId)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function getTotalFacturasByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return Factura::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('total');
    }
    
    public function getFacturasByMes(int $año, int $mes): Collection
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->whereYear('fecha', $año)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha', 'desc')
            ->get();
    }
    
    public function findByNumeroFactura(string $numeroFactura): ?Factura
    {
        return Factura::with(['cliente', 'facturaDetalles'])
            ->where('numero_factura', $numeroFactura)
            ->first();
    }
    
    public function getMetricas(): array
    {
        $hoy = now()->format('Y-m-d');
        $primerDiaMes = now()->startOfMonth()->format('Y-m-d');
        $ultimoDiaMes = now()->endOfMonth()->format('Y-m-d');

        $facturasHoy = $this->getByFechaRange($hoy, $hoy);
        $facturasMes = $this->getByFechaRange($primerDiaMes, $ultimoDiaMes);

        return [
            'total_facturas_hoy' => $facturasHoy->sum('total'),
            'total_facturas_mes' => $facturasMes->sum('total'),
            'cantidad_facturas_mes' => $facturasMes->count(),
            'promedio_factura_mes' => $facturasMes->avg('total') ?? 0,
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
