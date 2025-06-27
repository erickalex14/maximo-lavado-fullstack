<?php

namespace App\Http\Controllers;

use App\Services\EmpleadoService;
use App\Http\Requests\Empleado\CreateEmpleadoRequest;
use App\Http\Requests\Empleado\UpdateEmpleadoRequest;
use Illuminate\Http\JsonResponse;

class EmpleadoController extends Controller
{
    protected $empleadoService;

    public function __construct(EmpleadoService $empleadoService)
    {
        $this->empleadoService = $empleadoService;
    }

    /**
     * Display a listing of the empleados.
     */
    public function index(): JsonResponse
    {
        try {
            $empleados = $this->empleadoService->getEmpleadosWithLavados();
            
            if ($empleados->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay empleados registrados'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Empleados encontrados',
                'data' => $empleados
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener empleados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created empleado in storage.
     */
    public function store(CreateEmpleadoRequest $request): JsonResponse
    {
        try {
            $empleado = $this->empleadoService->createEmpleado($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Empleado creado correctamente',
                'data' => $empleado
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified empleado.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $empleado = $this->empleadoService->findEmpleadoById($id);
            
            if (!$empleado) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Empleado encontrado',
                'data' => $empleado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified empleado in storage.
     */
    public function update(UpdateEmpleadoRequest $request, int $id): JsonResponse
    {
        try {
            $empleado = $this->empleadoService->updateEmpleado($id, $request->validated());
            
            if (!$empleado) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Empleado actualizado correctamente',
                'data' => $empleado
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified empleado from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->empleadoService->deleteEmpleado($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Empleado eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of lavados by empleado and date.
     */
    public function lavadosPorDia(int $empleadoId, string $fecha): JsonResponse
    {
        try {
            $count = $this->empleadoService->getLavadosPorDia($empleadoId, $fecha);
            
            return response()->json([
                'status' => 'success',
                'data' => ['lavados' => $count]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener lavados por día: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of lavados by empleado and week.
     */
    public function lavadosPorSemana(int $empleadoId, string $fecha): JsonResponse
    {
        try {
            $count = $this->empleadoService->getLavadosPorSemana($empleadoId, $fecha);
            
            return response()->json([
                'status' => 'success',
                'data' => ['lavados' => $count]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener lavados por semana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get count of lavados by empleado and month.
     */
    public function lavadosPorMes(int $empleadoId, int $anio, int $mes): JsonResponse
    {
        try {
            $count = $this->empleadoService->getLavadosPorMes($empleadoId, $anio, $mes);
            
            return response()->json([
                'status' => 'success',
                'data' => ['lavados' => $count]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener lavados por mes: ' . $e->getMessage()
            ], 500);
        }
    }
}
