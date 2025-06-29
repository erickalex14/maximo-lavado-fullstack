<?php

namespace App\Repositories;

use App\Contracts\LavadoRepositoryInterface;
use App\Models\Lavado;
use App\Models\Ingreso;
use App\Models\Vehiculo;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LavadoRepository implements LavadoRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Lavado::with(['vehiculo.cliente', 'empleado']);

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('vehiculo.cliente', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            })->orWhereHas('vehiculo', function ($q) use ($search) {
                $q->where('matricula', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['vehiculo_id'])) {
            $query->where('vehiculo_id', $filters['vehiculo_id']);
        }

        if (!empty($filters['empleado_id'])) {
            $query->where('empleado_id', $filters['empleado_id']);
        }

        if (!empty($filters['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $filters['fecha_inicio']);
        }

        if (!empty($filters['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $filters['fecha_fin']);
        }

        return $query->orderBy('fecha', 'desc')->paginate($perPage);
    }

    public function getAll(array $filters = []): Collection
    {
        $query = Lavado::with(['vehiculo.cliente', 'empleado']);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    public function findById(int $id): ?Lavado
    {
        return Lavado::with(['vehiculo.cliente', 'empleado'])->find($id);
    }

    public function create(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // Verificar que el vehículo existe
                $vehiculo = Vehiculo::with('cliente')->find($data['vehiculo_id']);
                if (!$vehiculo) {
                    return ['success' => false, 'message' => 'Vehículo no encontrado'];
                }
                
                $data['fecha'] = $data['fecha'] ?? now()->format('Y-m-d');
                
                // Crear el lavado
                $lavado = Lavado::create($data);
                
                // Crear descripción para ingreso
                $descripcionIngreso = 'Lavado ' . $data['tipo_lavado'] . ' - ' . $vehiculo->cliente->nombre;
                if ($vehiculo->matricula) {
                    $descripcionIngreso .= ' (' . $vehiculo->matricula . ')';
                }
                
                // Crear el ingreso correspondiente
                $ingreso = Ingreso::create([
                    'fecha' => $data['fecha'],
                    'tipo' => 'lavado',
                    'referencia_id' => $lavado->lavado_id,
                    'monto' => $lavado->precio,
                    'descripcion' => $descripcionIngreso,
                ]);

                // Generar factura automáticamente
                $numeroFactura = $this->generateNumeroFactura();
                $descripcionFactura = 'Factura por servicio de lavado ' . $data['tipo_lavado'];
                
                $factura = Factura::create([
                    'numero_factura' => $numeroFactura,
                    'cliente_id' => $vehiculo->cliente_id,
                    'fecha' => $data['fecha'],
                    'descripcion' => $descripcionFactura,
                    'total' => $lavado->precio,
                ]);

                // Crear detalle de factura
                FacturaDetalle::create([
                    'factura_id' => $factura->factura_id,
                    'lavado_id' => $lavado->lavado_id,
                    'cantidad' => 1,
                    'precio_unitario' => $lavado->precio,
                    'subtotal' => $lavado->precio,
                ]);
                
                return [
                    'success' => true,
                    'lavado' => $lavado->load(['vehiculo.cliente', 'empleado']),
                    'ingreso' => $ingreso,
                    'factura' => $factura->load(['cliente', 'detalles'])
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al crear el lavado: ' . $e->getMessage()];
        }
    }

    public function update(int $id, array $data): Lavado
    {
        return DB::transaction(function () use ($id, $data) {
            $lavado = Lavado::findOrFail($id);
            $lavado->update($data);
            return $lavado->fresh(['vehiculo.cliente', 'empleado']);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $lavado = Lavado::findOrFail($id);
            
            // Soft delete la factura relacionada si existe
            $facturaDetalle = FacturaDetalle::where('lavado_id', $lavado->lavado_id)->first();
            if ($facturaDetalle) {
                $factura = $facturaDetalle->factura;
                // Soft delete detalles de factura
                $facturaDetalle->delete();
                // Soft delete factura si no tiene más detalles activos
                if ($factura->detalles()->count() == 0) {
                    $factura->delete();
                }
            }
            
            // Soft delete el ingreso relacionado si existe
            $ingreso = Ingreso::where('tipo', 'lavado')
                ->where('referencia_id', $lavado->lavado_id)
                ->first();
            
            if ($ingreso) {
                $ingreso->delete();
            }
            
            // Soft delete el lavado
            return $lavado->delete();
        });
    }

    /**
     * Restaurar un lavado eliminado lógicamente
     */
    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $lavado = Lavado::onlyTrashed()->findOrFail($id);
            
            // Restaurar el lavado
            $restored = $lavado->restore();
            
            if ($restored) {
                // Restaurar el ingreso relacionado si existe
                $ingreso = Ingreso::onlyTrashed()
                    ->where('tipo', 'lavado')
                    ->where('referencia_id', $lavado->lavado_id)
                    ->first();
                
                if ($ingreso) {
                    $ingreso->restore();
                }
                
                // Restaurar la factura relacionada si existe
                $facturaDetalle = FacturaDetalle::onlyTrashed()
                    ->where('lavado_id', $lavado->lavado_id)
                    ->first();
                    
                if ($facturaDetalle) {
                    $facturaDetalle->restore();
                    $factura = Factura::onlyTrashed()->find($facturaDetalle->factura_id);
                    if ($factura) {
                        $factura->restore();
                    }
                }
            }
            
            return $restored;
        });
    }

    /**
     * Obtener lavados eliminados lógicamente
     */
    public function getTrashed(): Collection
    {
        return Lavado::onlyTrashed()
            ->with(['vehiculo.cliente', 'empleado'])
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    public function getByCliente(int $clienteId): Collection
    {
        return Lavado::with(['empleado', 'vehiculo'])
                    ->whereHas('vehiculo', function($q) use ($clienteId) {
                        $q->where('cliente_id', $clienteId);
                    })
                    ->orderBy('fecha', 'desc')
                    ->get();
    }

    public function getByEmpleado(int $empleadoId, array $filters = []): Collection
    {
        $query = Lavado::with(['vehiculo.cliente'])
                    ->where('empleado_id', $empleadoId);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    public function getByVehiculo(int $vehiculoId, array $filters = []): Collection
    {
        $query = Lavado::with(['empleado', 'vehiculo.cliente'])
                    ->where('vehiculo_id', $vehiculoId);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    public function getByDay(string $fecha, array $filters = []): Collection
    {
        $query = Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->whereDate('fecha', $fecha);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    public function getByWeek(string $fecha, array $filters = []): Collection
    {
        $fechaCarbon = Carbon::parse($fecha);
        $inicioSemana = $fechaCarbon->startOfWeek();
        $finSemana = $fechaCarbon->copy()->endOfWeek();
        
        $query = Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->whereBetween('fecha', [$inicioSemana, $finSemana]);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    public function getByMonth(int $anio, int $mes, array $filters = []): Collection
    {
        $query = Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->whereYear('fecha', $anio)
                    ->whereMonth('fecha', $mes);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    public function getByYear(int $anio, array $filters = []): Collection
    {
        $query = Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->whereYear('fecha', $anio);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->orderBy('fecha', 'desc')
                    ->get();
    }

    public function getStats(array $filters = []): array
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $queryHoy = Lavado::whereDate('fecha', $hoy);
        $queryMes = Lavado::whereBetween('fecha', [$inicioMes, $finMes]);
        
        // Aplicar filtros a las consultas
        $this->applyFilters($queryHoy, $filters);
        $this->applyFilters($queryMes, $filters);
        
        $totalHoy = $queryHoy->count();
        $totalMes = $queryMes->count();
        $ingresosMes = $queryMes->sum('precio');
        $promedioDiario = $totalMes > 0 ? $ingresosMes / $hoy->day : 0;

        return [
            'total_hoy' => $totalHoy,
            'total_mes' => $totalMes,
            'ingresos_mes' => $ingresosMes,
            'promedio_diario' => round($promedioDiario, 2),
            'filtros_aplicados' => !empty($filters)
        ];
    }

    public function getRecientes(int $limit = 10): Collection
    {
        return Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->orderBy('fecha', 'desc')
                    ->limit($limit)
                    ->get();
    }

    // =================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =================================================================

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

    /**
     * Aplicar filtros comunes a las consultas
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['empleado_id'])) {
            $query->where('empleado_id', $filters['empleado_id']);
        }

        if (!empty($filters['vehiculo_id'])) {
            $query->where('vehiculo_id', $filters['vehiculo_id']);
        }

        if (!empty($filters['cliente_id'])) {
            $query->whereHas('vehiculo', function ($q) use ($filters) {
                $q->where('cliente_id', $filters['cliente_id']);
            });
        }

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['tipo_lavado'])) {
            $query->where('tipo_lavado', $filters['tipo_lavado']);
        }

        if (!empty($filters['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $filters['fecha_inicio']);
        }

        if (!empty($filters['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $filters['fecha_fin']);
        }

        if (!empty($filters['precio_min'])) {
            $query->where('precio', '>=', $filters['precio_min']);
        }

        if (!empty($filters['precio_max'])) {
            $query->where('precio', '<=', $filters['precio_max']);
        }
    }
}
