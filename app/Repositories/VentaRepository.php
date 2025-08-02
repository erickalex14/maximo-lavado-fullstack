<?php

namespace App\Repositories;

use App\Contracts\VentaRepositoryInterface;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaRepository implements VentaRepositoryInterface
{
    /**
     * Obtener todas las ventas
     */
    public function getAll(): Collection
    {
        return Venta::with(['cliente', 'detalles'])->orderBy('fecha', 'desc')->get();
    }

    /**
     * Obtener todas incluyendo eliminadas (soft deletes)
     */
    public function getAllWithTrashed(): Collection
    {
        return Venta::withTrashed()->with(['cliente', 'detalles'])->orderBy('fecha', 'desc')->get();
    }

    /**
     * Obtener solo las eliminadas (soft deletes)
     */
    public function getOnlyTrashed(): Collection
    {
        return Venta::onlyTrashed()->with(['cliente', 'detalles'])->orderBy('fecha', 'desc')->get();
    }

    /**
     * Buscar venta por ID
     */
    public function findById(int $id): ?Venta
    {
        return Venta::with(['cliente', 'detalles', 'facturaElectronica'])->find($id);
    }

    /**
     * Buscar venta por ID incluyendo eliminadas
     */
    public function findByIdWithTrashed(int $id): ?Venta
    {
        return Venta::withTrashed()->with(['cliente', 'detalles', 'facturaElectronica'])->find($id);
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
    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $venta = Venta::find($id);
            if ($venta) {
                return $venta->update($data);
            }
            return false;
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
     * ⚡ MÉTODO CRÍTICO: Procesar venta completa con flujos automáticos
     * Orquesta la creación de venta con todos los flujos integrados
     * 
     * @param array $data Datos completos de la venta (incluye venta y detalles)
     * @return array Resultado del procesamiento con venta y metadatos
     */
    public function procesarVentaCompleta(array $data): array
    {
        return DB::transaction(function () use ($data) {
            Log::info('🔄 Iniciando procesamiento de venta completa', [
                'cliente_id' => $data['cliente_id'] ?? null,
                'total_detalles' => count($data['detalles'] ?? [])
            ]);

            // Separar datos de venta y detalles
            $detallesData = $data['detalles'] ?? [];
            unset($data['detalles']);
            $ventaData = $data;

            // 1. Crear la venta base con detalles (solo persistencia)
            $venta = $this->createVentaCompleta($ventaData, $detallesData);

            Log::info('✅ Venta completa procesada exitosamente', [
                'venta_id' => $venta->venta_id,
                'cliente' => $venta->cliente->nombre,
                'total' => $venta->total,
                'detalles_count' => $venta->detalles->count()
            ]);

            return [
                'success' => true,
                'venta' => $venta,
                'message' => 'Venta procesada exitosamente'
            ];
        });
    }

    /**
     * Crear venta completa con detalles (solo persistencia)
     */
    public function createVentaCompleta(array $ventaData, array $detallesData): Venta
    {
        return DB::transaction(function () use ($ventaData, $detallesData) {
            Log::info('📦 Creando venta completa en repositorio', [
                'cliente_id' => $ventaData['cliente_id'] ?? null,
                'total_detalles' => count($detallesData)
            ]);

            // 1. Crear la venta base
            $venta = Venta::create($ventaData);
            Log::info('✅ Venta base creada', ['venta_id' => $venta->venta_id]);
            
            // 2. Crear los detalles de venta
            foreach ($detallesData as $detalleData) {
                $detalleData['venta_id'] = $venta->venta_id;
                $venta->detalles()->create($detalleData);
            }
            Log::info('✅ Detalles de venta creados', ['count' => count($detallesData)]);
            
            return $venta->fresh(['cliente', 'detalles']);
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
            
            return $venta->fresh(['cliente', 'detalles']);
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
    public function getVentasDelDia(?\DateTime $fecha = null): Collection
    {
        $fechaStr = $fecha ? $fecha->format('Y-m-d') : now()->format('Y-m-d');
        
        return Venta::whereDate('fecha', $fechaStr)
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
     * Obtener total de ventas del día por DateTime (para compatibilidad con interfaz)
     */
    private function getTotalVentasDelDiaDateTime(?\DateTime $fecha = null): float
    {
        $fechaStr = $fecha ? $fecha->format('Y-m-d') : now()->format('Y-m-d');
        return $this->getTotalVentasDelDia($fechaStr);
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

    /**
     * Validar stock disponible para los detalles de venta
     */
    public function validarStockDisponible(array $detalles): array
    {
        $resultado = ['valido' => true, 'errores' => []];
        
        foreach ($detalles as $detalle) {
            if (str_contains($detalle['vendible_type'], 'Producto')) {
                // Aquí se validaría el stock del producto
                // Por ahora retornamos válido
            }
        }
        
        return $resultado;
    }

    /**
     * Buscar ventas por cliente ID
     */
    public function findByClienteId(int $clienteId): Collection
    {
        return $this->getByClienteId($clienteId);
    }

    /**
     * Buscar ventas por empleado ID
     */
    public function findByEmpleadoId(int $empleadoId): Collection
    {
        return Venta::where('empleado_id', $empleadoId)
                   ->with(['cliente', 'detalles.vendible'])
                   ->orderBy('fecha', 'desc')
                   ->get();
    }

    /**
     * Buscar ventas por vehículo ID
     */
    public function findByVehiculoId(int $vehiculoId): Collection
    {
        return Venta::whereHas('lavados', function ($query) use ($vehiculoId) {
            $query->where('vehiculo_id', $vehiculoId);
        })->with(['cliente', 'detalles.vendible'])
          ->orderBy('fecha', 'desc')
          ->get();
    }

    /**
     * Buscar ventas en rango de fechas
     */
    public function findByFechaRango(\DateTime $fechaInicio, \DateTime $fechaFin): Collection
    {
        return $this->getByDateRange($fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d'));
    }

    /**
     * Obtener ventas con sus detalles
     */
    public function getVentasConDetalles(): Collection
    {
        return Venta::with(['cliente', 'detalles.vendible'])
                   ->orderBy('fecha', 'desc')
                   ->get();
    }

    /**
     * Calcular total de una venta
     */
    public function calcularTotal(int $ventaId): float
    {
        $venta = Venta::find($ventaId);
        return $venta ? $venta->total : 0.0;
    }

    /**
     * Obtener métricas de ventas
     */
    public function getMetricas(array $filtros = []): array
    {
        return $this->getStats();
    }

    /**
     * Obtener mejores clientes por volumen de ventas
     */
    public function getMejoresClientes(int $limite = 10): Collection
    {
        return Venta::select('cliente_id', DB::raw('COUNT(*) as total_ventas'), DB::raw('SUM(total) as total_facturado'))
                   ->with('cliente')
                   ->groupBy('cliente_id')
                   ->orderByDesc('total_facturado')
                   ->limit($limite)
                   ->get();
    }

    /**
     * Obtener productos/servicios más vendidos
     */
    public function getProductosMasVendidos(int $limite = 10): Collection
    {
        return DB::table('venta_detalles as vd')
                 ->select('vd.vendible_type', 'vd.vendible_id', DB::raw('SUM(vd.cantidad) as total_vendido'), DB::raw('SUM(vd.precio_unitario * vd.cantidad) as total_facturado'))
                 ->groupBy('vd.vendible_type', 'vd.vendible_id')
                 ->orderByDesc('total_vendido')
                 ->limit($limite)
                 ->get();
    }
}