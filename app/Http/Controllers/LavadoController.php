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
            
            return $this->successResponse('Lavados obtenidos correctamente', $lavados);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los lavados: ' . $e->getMessage(), 500);
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
            
            return $this->successResponse('Lavados por empleado obtenidos correctamente', $lavados);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados por empleado: ' . $e->getMessage(), 500);
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
            
            return $this->successResponse('Lavados por vehículo obtenidos correctamente', $lavados);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados por vehículo: ' . $e->getMessage(), 500);
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
            
            return $this->successResponse('Lavados del día obtenidos correctamente', $lavados);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados del día: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener lavados por semana
     */
    public function getByWeek(Request $request): JsonResponse
    {
        $request->validate(['fecha' => 'required|date']);
        
        try {
            $filters = $this->extractFilters($request);
            $lavados = $this->lavadoService->getLavadosByWeek($request->fecha, $filters);
            
            return $this->successResponse('Lavados de la semana obtenidos correctamente', $lavados);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados de la semana: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener lavados por mes
     */
    public function getByMonth(Request $request): JsonResponse
    {
        $request->validate([
            'anio' => 'required|integer|min:2020|max:2030',
            'mes' => 'required|integer|min:1|max:12'
        ]);
        
        try {
            $filters = $this->extractFilters($request);
            $lavados = $this->lavadoService->getLavadosByMonth($request->anio, $request->mes, $filters);
            
            return $this->successResponse('Lavados del mes obtenidos correctamente', $lavados);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados del mes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener lavados por año
     */
    public function getByYear(Request $request): JsonResponse
    {
        $request->validate(['anio' => 'required|integer|min:2020|max:2030']);
        
        try {
            $filters = $this->extractFilters($request);
            $lavados = $this->lavadoService->getLavadosByYear($request->anio, $filters);
            
            return $this->successResponse('Lavados del año obtenidos correctamente', $lavados);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados del año: ' . $e->getMessage(), 500);
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
            
            return $this->successResponse('Estadísticas obtenidas correctamente', $stats);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Guardar un nuevo lavado y crear ingreso automáticamente
     */
    public function store(CreateLavadoRequest $request): JsonResponse
    {
        try {
            $result = $this->lavadoService->createLavado($request->validated());
            
            if (!$result['success']) {
                return $this->errorResponse($result['message'], 400);
            }

            return $this->successResponse(
                'Lavado creado correctamente y registrado como ingreso',
                $result['data'],
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear el lavado: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Muestra un lavado específico por ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lavado = $this->lavadoService->findLavadoById($id);
            
            if (!$lavado) {
                return $this->errorResponse('Lavado no encontrado', 404);
            }

            return $this->successResponse('Lavado obtenido correctamente', $lavado);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener el lavado: ' . $e->getMessage(), 500);
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
                return $this->errorResponse('Lavado no encontrado', 404);
            }

            return $this->successResponse('Lavado actualizado correctamente', $lavado);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar el lavado: ' . $e->getMessage(), 500);
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
                return $this->errorResponse('No se pudo eliminar el lavado', 400);
            }

            return $this->successResponse('Lavado eliminado correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar el lavado: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Restaurar lavado eliminado lógicamente
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $result = $this->lavadoService->restoreLavado($id);
            
            if (!$result) {
                return $this->errorResponse('No se pudo restaurar el lavado', 400);
            }

            return $this->successResponse('Lavado restaurado correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al restaurar el lavado: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener lavados eliminados lógicamente
     */
    public function trashed(): JsonResponse
    {
        try {
            $lavados = $this->lavadoService->getTrashedLavados();
            
            return $this->successResponse('Lavados eliminados obtenidos correctamente', $lavados);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener lavados eliminados: ' . $e->getMessage(), 500);
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

    /**
     * Respuesta de éxito estandarizada
     */
    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Respuesta de error estandarizada
     */
    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $status);
    }
}
