<?php

namespace App\Services;

use App\Contracts\LavadoRepositoryInterface;
use App\Contracts\ServicioRepositoryInterface;
use App\Contracts\VentaRepositoryInterface;
use App\Models\Lavado;
use App\Models\Vehiculo;
use App\Services\ServicioService;
use App\Services\VentaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

/**
 * 🔄 LavadoService V2 - MIGRACIÓN GRADUAL AL SISTEMA UNIFICADO
 * 
 * Este servicio maneja la transición del sistema legacy de lavados
 * al nuevo sistema unificado de ventas y servicios
 */
class LavadoService
{
    protected LavadoRepositoryInterface $lavadoRepository;
    protected ServicioRepositoryInterface $servicioRepository;
    protected ServicioService $servicioService;

    public function __construct(
        LavadoRepositoryInterface $lavadoRepository,
        ServicioRepositoryInterface $servicioRepository,
        ServicioService $servicioService
    ) {
        $this->lavadoRepository = $lavadoRepository;
        $this->servicioRepository = $servicioRepository;
        $this->servicioService = $servicioService;
    }

    /**
     * ⚡ MÉTODO PRINCIPAL: Crear lavado usando el nuevo sistema unificado
     * Migra automáticamente a Venta + Servicio
     */
    public function crearLavado(array $data): array
    {
        try {
            Log::info('🔄 Creando lavado usando sistema unificado V2', [
                'vehiculo_id' => $data['vehiculo_id'],
                'tipo_lavado' => $data['tipo_lavado']
            ]);

            // PASO 1: Buscar/crear servicio equivalente al tipo de lavado
            $servicio = $this->obtenerServicioEquivalente($data['tipo_lavado'], $data['vehiculo_id']);

            // PASO 2: Crear venta unificada usando VentaService
            $ventaData = [
                'cliente_id' => $this->obtenerClienteDelVehiculo($data['vehiculo_id']),
                'empleado_id' => $data['empleado_id'] ?? null,
                'fecha' => $data['fecha'] ?? now()->format('Y-m-d'),
                'observaciones' => "Migrado desde lavado: {$data['tipo_lavado']}"
            ];

            $detallesVenta = [
                [
                    'vendible_type' => 'App\Models\Servicio',
                    'vendible_id' => $servicio->servicio_id,
                    'cantidad' => 1,
                    'precio_unitario' => $data['precio'],
                    'descripcion' => "Servicio de lavado {$data['tipo_lavado']}"
                ]
            ];

            // Crear venta completa usando el flujo automático
            $ventaService = resolve(VentaService::class);
            $venta = $ventaService->crearVentaCompleta($ventaData, $detallesVenta);

            // PASO 3: Crear registro legacy para compatibilidad
            $lavadoLegacy = null;
            // Mantener compatibilidad legacy por defecto
            $lavadoLegacy = $this->crearRegistroLegacy($data, $venta);

            Log::info('✅ Lavado creado exitosamente usando sistema unificado', [
                'venta_id' => $venta->venta_id,
                'servicio_id' => $servicio->servicio_id,
                'lavado_legacy_id' => $lavadoLegacy?->lavado_id,
                'total' => $venta->total
            ]);

            return [
                'success' => true,
                'venta' => $venta,
                'servicio' => $servicio,
                'lavado_legacy' => $lavadoLegacy,
                'message' => 'Lavado creado usando sistema unificado V2'
            ];

        } catch (\Exception $e) {
            Log::error('❌ Error al crear lavado usando sistema unificado', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            // 🔄 FALLBACK: Si falla el nuevo sistema, usar el legacy
            Log::warning('🔄 Fallback: Usando sistema legacy de lavados');
            return $this->crearLavadoLegacy($data);
        }
    }

    /**
     * ⚡ MÉTODO CRÍTICO: Crear lavados automáticos desde una venta con servicios
     * Usado por VentaService para flujos automáticos
     * 
     * @param \App\Models\Venta $venta La venta que contiene servicios
     * @return array Array de lavados creados
     */
    public function crearLavadosDesdeVenta($venta): array
    {
        try {
            Log::info('⚡ Iniciando creación de lavados desde venta', [
                'venta_id' => $venta->venta_id,
                'cliente_id' => $venta->cliente_id,
                'total_detalles' => $venta->detalles->count()
            ]);

            $lavadosCreados = [];
            
            // Filtrar solo los detalles que son servicios
            $detallesServicios = $venta->detalles->where('vendible_type', 'App\Models\Servicio');
            
            if ($detallesServicios->isEmpty()) {
                Log::info('No se encontraron servicios en la venta, no se crean lavados', [
                    'venta_id' => $venta->venta_id
                ]);
                return $lavadosCreados;
            }

            // Crear un lavado por cada servicio en la venta
            foreach ($detallesServicios as $detalle) {
                $servicio = $detalle->vendible;
                
                // Datos del lavado basados en la venta y el servicio
                $dataLavado = [
                    'venta_id' => $venta->venta_id,
                    'cliente_id' => $venta->cliente_id,
                    'empleado_id' => $venta->empleado_id ?? null,
                    'servicio_id' => $servicio->servicio_id,
                    'vehiculo_id' => null, // Se puede obtener del cliente o asignar después
                    'tipo_vehiculo_id' => null, // Se puede inferir del servicio
                    'estado' => 'PENDIENTE', // Estado inicial
                    'observaciones' => "Lavado creado automáticamente desde venta #{$venta->venta_id}",
                    'fecha' => $venta->fecha ?? now(),
                    'precio' => $detalle->precio_unitario,
                    'cantidad_servicios' => $detalle->cantidad,
                    
                    // Auditoría
                    'creado_desde_venta' => true,
                    'sistema_origen' => 'V2_AUTOMATICO',
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Intentar obtener vehículo del cliente
                if ($venta->cliente && $venta->cliente->vehiculos->isNotEmpty()) {
                    $vehiculoPrincipal = $venta->cliente->vehiculos->first();
                    $dataLavado['vehiculo_id'] = $vehiculoPrincipal->vehiculo_id;
                    $dataLavado['tipo_vehiculo_id'] = $vehiculoPrincipal->tipo_vehiculo_id;
                }

                // Crear el registro de lavado
                $lavado = $this->lavadoRepository->create($dataLavado);
                $lavadosCreados[] = $lavado;

                Log::info('Lavado creado desde venta', [
                    'lavado_id' => $lavado->lavado_id,
                    'venta_id' => $venta->venta_id,
                    'servicio_id' => $servicio->servicio_id,
                    'servicio_nombre' => $servicio->nombre,
                    'estado' => $lavado->estado
                ]);
            }

            Log::info('✅ Lavados creados exitosamente desde venta', [
                'venta_id' => $venta->venta_id,
                'lavados_creados' => count($lavadosCreados),
                'servicios_procesados' => $detallesServicios->count()
            ]);

            return $lavadosCreados;

        } catch (\Exception $e) {
            Log::error('❌ Error al crear lavados desde venta', [
                'venta_id' => $venta->venta_id ?? 'desconocido',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Error al crear lavados automáticos: {$e->getMessage()}");
        }
    }

    /**
     * 🔄 MÉTODO DE MIGRACIÓN: Migrar lavados legacy al sistema unificado
     */
    public function migrarLavadosLegacy(int $limit = 100): array
    {
        Log::info('🔄 Iniciando migración masiva de lavados legacy');

        $lavadosLegacy = Lavado::whereNull('migrado_a_venta_id')
                              ->limit($limit)
                              ->get();

        $migrados = 0;
        $errores = 0;
        $detalles = [];

        foreach ($lavadosLegacy as $lavado) {
            try {
                $resultado = $this->migrarLavadoIndividual($lavado);
                if ($resultado['success']) {
                    $migrados++;
                    $detalles[] = [
                        'lavado_id' => $lavado->lavado_id,
                        'venta_id' => $resultado['venta_id'],
                        'status' => 'migrado'
                    ];
                } else {
                    $errores++;
                    $detalles[] = [
                        'lavado_id' => $lavado->lavado_id,
                        'error' => $resultado['error'],
                        'status' => 'error'
                    ];
                }
            } catch (\Exception $e) {
                $errores++;
                $detalles[] = [
                    'lavado_id' => $lavado->lavado_id,
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }

        Log::info('🎯 Migración masiva completada', [
            'total_procesados' => count($lavadosLegacy),
            'migrados' => $migrados,
            'errores' => $errores
        ]);

        return [
            'total_procesados' => count($lavadosLegacy),
            'migrados' => $migrados,
            'errores' => $errores,
            'detalles' => $detalles
        ];
    }

    /**
     * Obtener lavados con paginación (método legacy mantenido por compatibilidad)
     */
    public function getLavadosPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->lavadoRepository->getAllPaginated($perPage, $filters);
    }

    /**
     * Obtener todos los lavados
     */
    public function getAllLavados(array $filters = []): Collection
    {
        return $this->lavadoRepository->getAll($filters);
    }

    /**
     * Obtener lavado por ID
     */
    public function getLavadoById(int $id): ?Lavado
    {
        return $this->lavadoRepository->findById($id);
    }

    /**
     * Actualizar lavado (redirige al sistema unificado si es posible)
     */
    public function updateLavado(int $id, array $data): Lavado
    {
        $lavado = $this->lavadoRepository->findById($id);
        
        if (!$lavado) {
            throw new \Exception("Lavado no encontrado con ID: {$id}");
        }

        // Si el lavado ya fue migrado, actualizar la venta unificada
        if ($lavado->migrado_a_venta_id) {
            Log::info('🔄 Actualizando lavado migrado usando sistema unificado', [
                'lavado_id' => $id,
                'venta_id' => $lavado->migrado_a_venta_id
            ]);

            // Actualizar la venta unificada
            $this->actualizarVentaDesdelavado($lavado->migrado_a_venta_id, $data);
        }

        // Actualizar el registro legacy
        return $this->lavadoRepository->update($id, $data);
    }

    /**
     * Eliminar lavado
     */
    public function deleteLavado(int $id): bool
    {
        $lavado = $this->lavadoRepository->findById($id);
        
        if (!$lavado) {
            throw new \Exception("Lavado no encontrado con ID: {$id}");
        }

        // Si el lavado fue migrado, eliminar también la venta unificada
        if ($lavado->migrado_a_venta_id) {
            Log::info('🔄 Eliminando lavado migrado desde sistema unificado', [
                'lavado_id' => $id,
                'venta_id' => $lavado->migrado_a_venta_id
            ]);

            $ventaService = resolve(VentaService::class);
            $ventaService->eliminarVenta($lavado->migrado_a_venta_id);
        }

        return $this->lavadoRepository->delete($id);
    }

    /**
     * Restaurar lavado (método legacy mantenido por compatibilidad)
     */
    public function restoreLavado(int $id): bool
    {
        return $this->lavadoRepository->restore($id);
    }

    /**
     * Obtener lavados eliminados (método legacy mantenido por compatibilidad)
     */
    public function getTrashedLavados(): Collection
    {
        return $this->lavadoRepository->getTrashed();
    }

    /**
     * Obtener estadísticas de lavados
     */
    public function getEstadisticas(array $filters = []): array
    {
        $estadisticasLegacy = $this->lavadoRepository->getStats($filters);
        
        // Agregar estadísticas de migración
        $totalLavados = Lavado::count();
        $migrados = Lavado::whereNotNull('migrado_a_venta_id')->count();
        $pendientesMigracion = $totalLavados - $migrados;

        return array_merge($estadisticasLegacy, [
            'migracion' => [
                'total_lavados' => $totalLavados,
                'migrados' => $migrados,
                'pendientes_migracion' => $pendientesMigracion,
                'porcentaje_migrado' => $totalLavados > 0 ? round(($migrados / $totalLavados) * 100, 2) : 0
            ]
        ]);
    }

    /**
     * Obtener lavados por cliente (método legacy mantenido por compatibilidad)
     */
    public function getLavadosByCliente(int $clienteId): Collection
    {
        return $this->lavadoRepository->getByCliente($clienteId);
    }

    /**
     * Obtener lavados por empleado (método legacy mantenido por compatibilidad)
     */
    public function getLavadosByEmpleado(int $empleadoId, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByEmpleado($empleadoId, $filters);
    }

    /**
     * Obtener lavados por vehículo (método legacy mantenido por compatibilidad)
     */
    public function getLavadosByVehiculo(int $vehiculoId, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByVehiculo($vehiculoId, $filters);
    }

    /**
     * Obtener lavados recientes (método legacy mantenido por compatibilidad)
     */
    public function getLavadosRecientes(int $limit = 10): Collection
    {
        return $this->lavadoRepository->getRecientes($limit);
    }

    /**
     * Obtener lavados por día
     */
    public function getLavadosByDay(string $fecha, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByDay($fecha, $filters);
    }

    /**
     * Obtener lavados por semana
     */
    public function getLavadosByWeek(string $fecha, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByWeek($fecha, $filters);
    }

    /**
     * Obtener lavados por mes
     */
    public function getLavadosByMonth(int $anio, int $mes, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByMonth($anio, $mes, $filters);
    }

    /**
     * Obtener lavados por año
     */
    public function getLavadosByYear(int $anio, array $filters = []): Collection
    {
        return $this->lavadoRepository->getByYear($anio, $filters);
    }

    /**
     * Obtener lavados por rango de fechas
     */
    public function getLavadosByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->lavadoRepository->getByDateRange($fechaInicio, $fechaFin);
    }

    // =========================================================================
    // MÉTODOS PRIVADOS DE MIGRACIÓN Y COMPATIBILIDAD
    // =========================================================================

    /**
     * Obtener o crear servicio equivalente al tipo de lavado
     */
    private function obtenerServicioEquivalente(string $tipoLavado, int $vehiculoId): object
    {
        // Mapeo de tipos de lavado legacy a servicios
        $mapeoServicios = [
            'basico' => 'Lavado Básico',
            'completo' => 'Lavado Completo',
            'premium' => 'Lavado Premium',
            'express' => 'Lavado Express',
            'detallado' => 'Lavado Detallado'
        ];

        $nombreServicio = $mapeoServicios[$tipoLavado] ?? "Lavado {$tipoLavado}";

        // Buscar servicio existente
        $servicio = $this->servicioRepository->findByNombre($nombreServicio);

        // Si no existe, crearlo
        if (!$servicio) {
            $vehiculo = Vehiculo::with('tipoVehiculo')->find($vehiculoId);
            
            $servicioData = [
                'nombre' => $nombreServicio,
                'descripcion' => "Servicio de {$nombreServicio} migrado desde sistema legacy",
                'activo' => true
            ];

            $servicio = $this->servicioService->createServicio($servicioData);

            // Crear precios por tipo de vehículo si es necesario
            if ($vehiculo && $vehiculo->tipoVehiculo) {
                $this->servicioService->updatePrecio($servicio->servicio_id, $vehiculo->tipoVehiculo->tipo_vehiculo_id, 15.00);
            }

            Log::info('✅ Servicio creado automáticamente durante migración', [
                'servicio_id' => $servicio->servicio_id,
                'nombre' => $nombreServicio,
                'tipo_lavado_legacy' => $tipoLavado
            ]);
        }

        return $servicio;
    }

    /**
     * Obtener cliente del vehículo
     */
    private function obtenerClienteDelVehiculo(int $vehiculoId): int
    {
        $vehiculo = Vehiculo::find($vehiculoId);
        
        if (!$vehiculo || !$vehiculo->cliente_id) {
            throw new \Exception("Vehículo no encontrado o sin cliente asociado");
        }

        return $vehiculo->cliente_id;
    }

    /**
     * Crear registro legacy para compatibilidad
     */
    private function crearRegistroLegacy(array $data, object $venta): ?Lavado
    {
        try {
            $lavadoData = array_merge($data, [
                'migrado_a_venta_id' => $venta->venta_id,
                'migrado_at' => now()
            ]);

            $resultado = $this->lavadoRepository->create($lavadoData);
            
            return $resultado['success'] ? $resultado['lavado'] : null;
        } catch (\Exception $e) {
            Log::warning('⚠️ No se pudo crear registro legacy de compatibilidad', [
                'venta_id' => $venta->venta_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Crear lavado usando sistema legacy (fallback)
     */
    private function crearLavadoLegacy(array $data): array
    {
        Log::info('🔄 Usando sistema legacy para crear lavado');
        return $this->lavadoRepository->create($data);
    }

    /**
     * Migrar un lavado individual al sistema unificado
     */
    private function migrarLavadoIndividual(Lavado $lavado): array
    {
        try {
            // Preparar datos para el nuevo sistema
            $data = [
                'vehiculo_id' => $lavado->vehiculo_id,
                'tipo_lavado' => $lavado->tipo_lavado,
                'precio' => $lavado->precio,
                'empleado_id' => $lavado->empleado_id,
                'fecha' => $lavado->fecha,
            ];

            $resultado = $this->crearLavado($data);

            if ($resultado['success'] && $resultado['venta']) {
                // Marcar lavado como migrado
                $lavado->update([
                    'migrado_a_venta_id' => $resultado['venta']->venta_id,
                    'migrado_at' => now()
                ]);

                return [
                    'success' => true,
                    'venta_id' => $resultado['venta']->venta_id
                ];
            }

            return [
                'success' => false,
                'error' => 'No se pudo crear venta unificada'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar venta desde datos de lavado
     */
    private function actualizarVentaDesdelavado(int $ventaId, array $datosLavado): void
    {
        // Mapear datos de lavado a datos de venta
        $tipoLavado = isset($datosLavado['tipo_lavado']) ? $datosLavado['tipo_lavado'] : '';
        $ventaData = [
            'empleado_id' => $datosLavado['empleado_id'] ?? null,
            'fecha' => $datosLavado['fecha'] ?? null,
            'observaciones' => "Actualizado desde lavado: {$tipoLavado}"
        ];

        $detallesVenta = [];
        if (isset($datosLavado['precio'])) {
            // Actualizar precio del servicio en el detalle
            $ventaService = resolve(VentaService::class);
            $venta = $ventaService->getById($ventaId);
            if ($venta && $venta->detalles->isNotEmpty()) {
                $detallesVenta = $venta->detalles->map(function ($detalle) use ($datosLavado) {
                    return [
                        'vendible_type' => $detalle->vendible_type,
                        'vendible_id' => $detalle->vendible_id,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $datosLavado['precio'],
                        'descripcion' => $detalle->descripcion
                    ];
                })->toArray();
            }
        }

        if (!empty($detallesVenta)) {
            $ventaService = resolve(VentaService::class);
            $ventaService->actualizarVentaCompleta($ventaId, $ventaData, $detallesVenta);
        }
    }
}
