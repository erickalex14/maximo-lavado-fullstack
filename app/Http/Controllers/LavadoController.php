<?php

namespace App\Http\Controllers;

use App\Services\LavadoService;
use App\Http\Requests\Lavado\CreateLavadoRequest;
use App\Http\Requests\Lavado\UpdateLavadoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LavadoController extends Controller
{
    protected $lavadoService;

    public function __construct(LavadoService $lavadoService)
    {
        $this->lavadoService = $lavadoService;
    }

    /**
     * Lista de lavados con filtros opcionales
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $this->extractFilters($request);
            $lavados = $this->lavadoService->getAllLavados($filters);
            
            return $this->successResponse($lavados, 'lavados', 'Lavados obtenidos correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los lavados', $e);
        }
    }

    /**
     * Obtener lavados filtrados por empleado
     */
    public function getByEmpleado(int $empleadoId, Request $request): JsonResponse
    {
        try {
            $filters = $this->extractFilters($request);
            $lavados = $this->lavadoService->getLavadosByEmpleado($empleadoId, $filters);
            
            return $this->successResponse($lavados, 'lavados', 'Lavados por empleado obtenidos correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados por empleado', $e);
        }
    }

    /**
     * Obtener lavados filtrados por vehículo
     */
    public function getByVehiculo(int $vehiculoId, Request $request): JsonResponse
    {
        try {
            $filters = $this->extractFilters($request);
            $lavados = $this->lavadoService->getLavadosByVehiculo($vehiculoId, $filters);
            
            return $this->successResponse($lavados, 'lavados', 'Lavados por vehículo obtenidos correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados por vehículo', $e);
        }
    }

    /**
     * Obtener lavados por día específico
     */
    public function getByDay(Request $request): JsonResponse
    {
        $request->validate(['fecha' => 'required|date']);
        
        try {
            $filters = $this->extractFilters($request);
            $lavados = $this->lavadoService->getLavadosByDay($request->fecha, $filters);
            
            return $this->successResponse($lavados, 'lavados', 'Lavados del día obtenidos correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados del día', $e);
        }
    }

    /**
     * Guardar un nuevo lavado y crear ingreso automáticamente
     */
    public function store(CreateLavadoRequest $request): JsonResponse
    {
        try {
            $result = $this->lavadoService->crearLavado($request->validated());
            
            if (!$result['success']) {
                return $this->errorResponse($result['message']);
            }

            return $this->successResponse(
                $result['data'] ?? $result,
                'lavado',
                'Lavado creado correctamente y registrado como ingreso',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el lavado', $e);
        }
    }

    /**
     * Muestra un lavado específico por ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lavado = $this->lavadoService->getLavadoById($id);
            
            if (!$lavado) {
                return $this->notFoundResponse('Lavado no encontrado');
            }

            return $this->successResponse($lavado, 'lavado', 'Lavado obtenido correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener el lavado', $e);
        }
    }

    /**
     * Actualizar un lavado específico por ID
     */
    public function update(UpdateLavadoRequest $request, int $id): JsonResponse
    {
        try {
            $lavado = $this->lavadoService->updateLavado($id, $request->validated());
            
            if (!$lavado) {
                return $this->notFoundResponse('Lavado no encontrado');
            }

            return $this->successResponse($lavado, 'lavado', 'Lavado actualizado correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el lavado', $e);
        }
    }

    /**
     * Eliminar lavado (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->lavadoService->deleteLavado($id);
            
            if (!$result) {
                return $this->errorResponse('No se pudo eliminar el lavado');
            }

            return $this->successResponse(null, null, 'Lavado eliminado correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar el lavado', $e);
        }
    }

    /**
     * Obtener estadísticas de lavados
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $filters = $this->extractFilters($request);
            $stats = $this->lavadoService->getEstadisticas($filters);
            
            return $this->successResponse($stats, 'estadisticas', 'Estadísticas obtenidas correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e);
        }
    }

    /**
     * Obtener lavados eliminados lógicamente
     */
    public function trashed(): JsonResponse
    {
        try {
            $lavados = $this->lavadoService->getTrashedLavados();
            
            return $this->successResponse($lavados, 'lavados_eliminados', 'Lavados eliminados obtenidos correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados eliminados', $e);
        }
    }

    // =================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =================================================================

    /**
     * Extraer filtros de la request
     */
    private function extractFilters(Request $request): array
    {
        return array_filter([
            'empleado_id' => $request->input('empleado_id'),
            'vehiculo_id' => $request->input('vehiculo_id'),
            'cliente_id' => $request->input('cliente_id'),
            'estado' => $request->input('estado'),
            'tipo_lavado' => $request->input('tipo_lavado'),
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'precio_min' => $request->input('precio_min'),
            'precio_max' => $request->input('precio_max')
        ]);
    }
}
