<?php

namespace App\Repositories;

use App\Contracts\LavadoRepositoryInterface;
use App\Models\Lavado;
use App\Models\Ingreso;
use App\Models\Vehiculo;
use App\Models\FacturaElectronica;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * 🔄 LavadoRepository V2 - SOPORTE PARA MIGRACIÓN AL SISTEMA UNIFICADO
 * 
 * Mantiene compatibilidad con sistema legacy mientras soporta migración
 * gradual al nuevo sistema unificado de ventas y servicios
 */
class LavadoRepository implements LavadoRepositoryInterface
{
    /**
     * Obtener lavados paginados con filtros
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Lavado::with(['vehiculo.cliente', 'vehiculo.tipoVehiculo', 'empleado']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('fecha', 'desc')->paginate($perPage);
    }

    /**
     * Obtener todos los lavados con filtros
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Lavado::with(['vehiculo.cliente', 'vehiculo.tipoVehiculo', 'empleado']);
        
        $this->applyFilters($query, $filters);
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    /**
     * Buscar lavado por ID
     */
    public function findById(int $id): ?Lavado
    {
        return Lavado::with(['vehiculo.cliente', 'vehiculo.tipoVehiculo', 'empleado'])->find($id);
    }

    /**
     * Buscar lavado por nombre (para compatibilidad con interfaces estandarizadas)
     */
    public function findByNombre(string $nombre): ?Lavado
    {
        return Lavado::where('tipo_lavado', $nombre)->first();
    }

    /**
     * ⚡ CREAR LAVADO V2 - Con soporte para migración
     */
    public function create(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                Log::info('🔄 Creando lavado en repository V2', [
                    'vehiculo_id' => $data['vehiculo_id'],
                    'tipo_lavado' => $data['tipo_lavado'] ?? 'no_especificado'
                ]);

                // Verificar que el vehículo existe con tipo de vehículo
                $vehiculo = Vehiculo::with(['cliente', 'tipoVehiculo'])->find($data['vehiculo_id']);
                if (!$vehiculo) {
                    return ['success' => false, 'message' => 'Vehículo no encontrado'];
                }

                if (!$vehiculo->cliente) {
                    return ['success' => false, 'message' => 'Vehículo sin cliente asociado'];
                }
                
                $data['fecha'] = $data['fecha'] ?? now()->format('Y-m-d');
                
                // Crear el lavado con campos de migración V2
                $lavadoData = array_merge($data, [
                    'migrado_a_venta_id' => $data['migrado_a_venta_id'] ?? null,
                    'migrado_at' => $data['migrado_at'] ?? null,
                ]);

                $lavado = Lavado::create($lavadoData);
                
                // Solo crear ingreso y factura si NO está siendo migrado desde el sistema unificado
                $ingreso = null;
                $factura = null;

                if (!isset($data['migrado_a_venta_id'])) {
                    // ⚠️ SISTEMA LEGACY: Crear ingreso y factura manual
                    $ingreso = $this->crearIngresoLegacy($lavado, $vehiculo);
                    $factura = $this->crearFacturaLegacy($lavado, $vehiculo);
                    
                    Log::info('📄 Creados ingreso y factura legacy', [
                        'lavado_id' => $lavado->lavado_id,
                        'ingreso_id' => $ingreso?->ingreso_id,
                        'factura_id' => $factura?->factura_id
                    ]);
                } else {
                    Log::info('🔄 Lavado creado como parte de migración, no se crean documentos legacy', [
                        'lavado_id' => $lavado->lavado_id,
                        'venta_id' => $data['migrado_a_venta_id']
                    ]);
                }
                
                return [
                    'success' => true,
                    'lavado' => $lavado->load(['vehiculo.cliente', 'vehiculo.tipoVehiculo', 'empleado']),
                    'ingreso' => $ingreso,
                    'factura' => $factura
                ];
            });
        } catch (\Exception $e) {
            Log::error('❌ Error al crear lavado en repository V2', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return ['success' => false, 'message' => 'Error al crear el lavado: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar lavado
     */
    public function update(int $id, array $data): Lavado
    {
        return DB::transaction(function () use ($id, $data) {
            Log::info('🔄 Actualizando lavado', [
                'lavado_id' => $id,
                'campos_actualizar' => array_keys($data)
            ]);

            $lavado = Lavado::findOrFail($id);
            $lavado->update($data);
            
            Log::info('✅ Lavado actualizado exitosamente', [
                'lavado_id' => $id
            ]);

            return $lavado->fresh(['vehiculo.cliente', 'vehiculo.tipoVehiculo', 'empleado']);
        });
    }

    /**
     * Eliminar lavado (soft delete) con cascade a documentos relacionados
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            Log::info('🗑️ Eliminando lavado', ['lavado_id' => $id]);

            $lavado = Lavado::findOrFail($id);
            
            // Buscar factura electrónica relacionada directamente con el lavado (si existe en datos legacy)
            // Nota: En el nuevo sistema, los lavados se manejan a través de ventas
            $factura = FacturaElectronica::whereHas('venta.detalles', function($query) use ($lavado) {
                $query->where('referencia_id', $lavado->lavado_id)
                      ->where('tipo_item', 'servicio');
            })->first();
            
            if ($factura) {
                $factura->delete();
                Log::info('📄 Factura eliminada junto con lavado', [
                    'factura_id' => $factura->factura_electronica_id
                ]);
            }
            
            // Soft delete el ingreso relacionado si existe
            $ingreso = Ingreso::where('tipo', 'lavado')
                ->where('referencia_id', $lavado->lavado_id)
                ->first();
            
            if ($ingreso) {
                $ingreso->delete();
                Log::info('💰 Ingreso eliminado junto con lavado', [
                    'ingreso_id' => $ingreso->ingreso_id
                ]);
            }
            
            // Soft delete el lavado
            $resultado = $lavado->delete();
            
            Log::info('✅ Lavado eliminado exitosamente', [
                'lavado_id' => $id
            ]);

            return $resultado;
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
                $factura = FacturaElectronica::onlyTrashed()
                    ->whereHas('venta.detalles', function($query) use ($lavado) {
                        $query->where('referencia_id', $lavado->lavado_id)
                              ->where('tipo_item', 'servicio');
                    })->first();
                    
                if ($factura) {
                    $factura->restore();
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

        // Clonar builders para evitar side effects entre count/sum (cada llamada genera consulta nueva, pero mantenemos claridad)
        $totalHoy = (clone $queryHoy)->count();
        $ingresosHoy = (clone $queryHoy)->sum('precio');
        $totalMes = (clone $queryMes)->count();
        $ingresosMes = (clone $queryMes)->sum('precio');
        $promedioDiario = $hoy->day > 0 ? ($ingresosMes / $hoy->day) : 0;

        return [
            'total_hoy' => $totalHoy,
            'ingresos_hoy' => round($ingresosHoy, 2),
            'total_mes' => $totalMes,
            'ingresos_mes' => round($ingresosMes, 2),
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
        $lastFactura = FacturaElectronica::where('secuencial', 'like', $prefix . $year . $month . '%')
            ->orderBy('secuencial', 'desc')
            ->first();
        
        if ($lastFactura) {
            // Extraer el número secuencial del último número de factura
            $lastNumber = (int) substr($lastFactura->secuencial, -4);
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

        // 🔄 FILTROS V2: Soporte para migración
        if (isset($filters['migrado'])) {
            if ($filters['migrado']) {
                $query->whereNotNull('migrado_a_venta_id');
            } else {
                $query->whereNull('migrado_a_venta_id');
            }
        }
    }

    // =========================================================================
    // MÉTODOS AUXILIARES V2 - SOPORTE PARA SISTEMA LEGACY
    // =========================================================================

    /**
     * Crear ingreso legacy (solo cuando no es migración)
     */
    private function crearIngresoLegacy(Lavado $lavado, $vehiculo): ?Ingreso
    {
        try {
            // Crear descripción para ingreso
            $descripcionIngreso = 'Lavado ' . $lavado->tipo_lavado . ' - ' . $vehiculo->cliente->nombre;
            if ($vehiculo->matricula) {
                $descripcionIngreso .= ' (' . $vehiculo->matricula . ')';
            }
            
            // Crear el ingreso correspondiente
            return Ingreso::create([
                'fecha' => $lavado->fecha,
                'tipo' => 'lavado',
                'referencia_id' => $lavado->lavado_id,
                'monto' => $lavado->precio,
                'descripcion' => $descripcionIngreso,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al crear ingreso legacy', [
                'lavado_id' => $lavado->lavado_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Crear factura legacy (solo cuando no es migración)
     */
    private function crearFacturaLegacy(Lavado $lavado, $vehiculo): ?FacturaElectronica
    {
        try {
            // Generar factura automáticamente usando el nuevo sistema
            $numeroSecuencial = $this->generateNextSecuencial();
            $descripcionFactura = 'Factura por servicio de lavado ' . $lavado->tipo_lavado;
            
            // Crear factura electrónica
            $factura = FacturaElectronica::create([
                'venta_id' => null, // Legacy, sin venta asociada
                'ruc_emisor' => '1234567890001',
                'razon_social_emisor' => 'Máximo Lavado',
                'direccion_emisor' => 'Av. Principal 123, Quito',
                'establecimiento' => 1,
                'punto_emision' => 1,
                'secuencial' => $numeroSecuencial,
                'identificacion_comprador' => $vehiculo->cliente->cedula ?? '9999999999999',
                'razon_social_comprador' => $vehiculo->cliente->nombre,
                'direccion_comprador' => $vehiculo->cliente->direccion,
                'email_comprador' => $vehiculo->cliente->email,
                'tipo_documento' => 'factura',
                'ambiente' => 'pruebas',
                'tipo_emision' => 'normal',
                'estado_sri' => 'BORRADOR',
                'subtotal' => $lavado->precio / 1.12, // Sin IVA
                'descuento' => 0,
                'iva' => $lavado->precio - ($lavado->precio / 1.12), // 12% IVA
                'total' => $lavado->precio,
            ]);

            return $factura;
        } catch (\Exception $e) {
            Log::error('❌ Error al crear factura legacy', [
                'lavado_id' => $lavado->lavado_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generar siguiente secuencial para factura
     */
    private function generateNextSecuencial(): int
    {
        $lastFactura = FacturaElectronica::orderBy('secuencial', 'desc')->first();
        return $lastFactura ? $lastFactura->secuencial + 1 : 1;
    }
}
