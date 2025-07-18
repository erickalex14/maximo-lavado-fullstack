<?php

namespace App\Repositories;

use App\Contracts\VentaRepositoryInterface;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VentaRepository implements VentaRepositoryInterface
{
    /**
     * Obtener todas las ventas
     */
    public function getAll(): Collection
    {
        return Venta::with(['cliente', 'detalles.vendible'])->orderBy('fecha', 'desc')->get();
    }

    /**
     * Obtener todas incluyendo eliminadas (soft deletes)
     */
    public function getAllWithTrashed(): Collection
    {
        return Venta::withTrashed()->with(['cliente', 'detalles.vendible'])->orderBy('fecha', 'desc')->get();
    }

    /**
     * Obtener solo las eliminadas (soft deletes)
     */
    public function getOnlyTrashed(): Collection
    {
        return Venta::onlyTrashed()->with(['cliente', 'detalles.vendible'])->orderBy('fecha', 'desc')->get();
    }

    /**
     * Buscar venta por ID
     */
    public function findById(int $id): ?Venta
    {
        return Venta::with(['cliente', 'detalles.vendible', 'facturaElectronica'])->find($id);
    }

    /**
     * Buscar venta por ID incluyendo eliminadas
     */
    public function findByIdWithTrashed(int $id): ?Venta
    {
        return Venta::withTrashed()->with(['cliente', 'detalles.vendible', 'facturaElectronica'])->find($id);
    }

    /**
     * Crear nueva venta
     */
    public function create(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            return Venta::create($data);
        });
    }

    /**
     * Actualizar venta
     */
    public function update(int $id, array $data): ?Venta
    {
        return DB::transaction(function () use ($id, $data) {
            $venta = Venta::find($id);
            if ($venta) {
                $venta->update($data);
                return $venta->fresh();
            }
            return null;
        });
    }

    /**
     * Eliminar venta (soft delete)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = Venta::find($id);
            return $venta ? $venta->delete() : false;
        });
    }

    /**
     * Restaurar venta eliminada
     */
    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = Venta::onlyTrashed()->find($id);
            return $venta ? $venta->restore() : false;
        });
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = Venta::withTrashed()->find($id);
            return $venta ? $venta->forceDelete() : false;
        });
    }

    /**
     * Crear venta completa con detalles
     */
    public function createVentaCompleta(array $ventaData, array $detallesData): Venta
    {
        return DB::transaction(function () use ($ventaData, $detallesData) {
            // Crear la venta
            $venta = Venta::create($ventaData);
            
            // Crear los detalles
            foreach ($detallesData as $detalleData) {
                $detalleData['venta_id'] = $venta->venta_id;
                $venta->detalles()->create($detalleData);
            }
            
            return $venta->fresh(['cliente', 'detalles.vendible']);
        });
    }

    /**
     * Actualizar venta completa con detalles
     */
    public function updateVentaCompleta(int $id, array $ventaData, array $detallesData): ?Venta
    {
        return DB::transaction(function () use ($id, $ventaData, $detallesData) {
            $venta = Venta::find($id);
            if (!$venta) {
                return null;
            }
            
            // Actualizar la venta
            $venta->update($ventaData);
            
            // Eliminar detalles existentes
            $venta->detalles()->delete();
            
            // Crear nuevos detalles
            foreach ($detallesData as $detalleData) {
                $detalleData['venta_id'] = $venta->venta_id;
                $venta->detalles()->create($detalleData);
            }
            
            return $venta->fresh(['cliente', 'detalles.vendible']);
        });
    }

    /**
     * Obtener ventas por cliente
     */
    public function getByClienteId(int $clienteId): Collection
    {
        return Venta::where('cliente_id', $clienteId)
                   ->with(['detalles.vendible', 'facturaElectronica'])
                   ->orderBy('fecha', 'desc')
                   ->get();
    }

    /**
     * Obtener ventas por rango de fechas
     */
    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
                   ->with(['cliente', 'detalles.vendible'])
                   ->orderBy('fecha', 'desc')
                   ->get();
    }

    /**
     * Obtener ventas del día
     */
    public function getVentasDelDia(?string $fecha = null): Collection
    {
        $fecha = $fecha ?? now()->format('Y-m-d');
        
        return Venta::whereDate('fecha', $fecha)
                   ->with(['cliente', 'detalles.vendible'])
                   ->orderBy('fecha', 'desc')
                   ->get();
    }

    /**
     * Obtener ventas del mes
     */
    public function getVentasDelMes(int $year, int $month): Collection
    {
        return Venta::whereYear('fecha', $year)
                   ->whereMonth('fecha', $month)
                   ->with(['cliente', 'detalles.vendible'])
                   ->orderBy('fecha', 'desc')
                   ->get();
    }

    /**
     * Obtener total de ventas por rango de fechas
     */
    public function getTotalVentasByDateRange(string $fechaInicio, string $fechaFin): float
    {
        return Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
                   ->sum('total');
    }

    /**
     * Obtener total de ventas del día
     */
    public function getTotalVentasDelDia(?string $fecha = null): float
    {
        $fecha = $fecha ?? now()->format('Y-m-d');
        
        return Venta::whereDate('fecha', $fecha)->sum('total');
    }

    /**
     * Obtener total de ventas del mes
     */
    public function getTotalVentasDelMes(int $year, int $month): float
    {
        return Venta::whereYear('fecha', $year)
                   ->whereMonth('fecha', $month)
                   ->sum('total');
    }

    /**
     * Obtener estadísticas de ventas
     */
    public function getStats(): array
    {
        $hoy = now()->format('Y-m-d');
        $mesActual = now();
        
        return [
            'total_ventas' => Venta::count(),
            'ventas_hoy' => Venta::whereDate('fecha', $hoy)->count(),
            'ventas_mes' => Venta::whereYear('fecha', $mesActual->year)
                                 ->whereMonth('fecha', $mesActual->month)
                                 ->count(),
            'total_facturado_hoy' => $this->getTotalVentasDelDia($hoy),
            'total_facturado_mes' => $this->getTotalVentasDelMes($mesActual->year, $mesActual->month),
            'eliminadas' => Venta::onlyTrashed()->count(),
        ];
    }

    /**
     * Buscar ventas por término
     */
    public function search(string $term): Collection
    {
        return Venta::where(function ($query) use ($term) {
            $query->whereHas('cliente', function ($q) use ($term) {
                $q->where('nombre', 'ILIKE', "%{$term}%")
                  ->orWhere('telefono', 'ILIKE', "%{$term}%")
                  ->orWhere('cedula', 'ILIKE', "%{$term}%");
            });
        })->with(['cliente', 'detalles.vendible'])->get();
    }

    /**
     * Obtener ventas con productos específicos
     */
    public function getVentasConProductos(): Collection
    {
        return Venta::whereHas('detalles', function ($query) {
            $query->where('vendible_type', 'LIKE', '%Producto%');
        })->with(['cliente', 'detalles.vendible'])->get();
    }

    /**
     * Obtener ventas con servicios específicos
     */
    public function getVentasConServicios(): Collection
    {
        return Venta::whereHas('detalles', function ($query) {
            $query->where('vendible_type', 'App\Models\Servicio');
        })->with(['cliente', 'detalles.vendible'])->get();
    }

    /**
     * Obtener ventas mixtas (productos + servicios)
     */
    public function getVentasMixtas(): Collection
    {
        return Venta::whereHas('detalles', function ($query) {
            $query->where('vendible_type', 'LIKE', '%Producto%');
        })->whereHas('detalles', function ($query) {
            $query->where('vendible_type', 'App\Models\Servicio');
        })->with(['cliente', 'detalles.vendible'])->get();
    }

    /**
     * Verificar si una venta tiene factura electrónica
     */
    public function tieneFacturaElectronica(int $ventaId): bool
    {
        return Venta::find($ventaId)?->facturaElectronica()->exists() ?? false;
    }

    /**
     * Obtener ventas sin factura electrónica
     */
    public function getVentasSinFacturaElectronica(): Collection
    {
        return Venta::whereDoesntHave('facturaElectronica')
                   ->with(['cliente', 'detalles.vendible'])
                   ->get();
    }

    /**
     * Calcular totales por método de pago
     */
    public function getTotalesPorMetodoPago(string $fechaInicio, string $fechaFin): array
    {
        return Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
                   ->selectRaw('metodo_pago, SUM(total) as total_metodo, COUNT(*) as cantidad_ventas')
                   ->groupBy('metodo_pago')
                   ->get()
                   ->toArray();
    }
}