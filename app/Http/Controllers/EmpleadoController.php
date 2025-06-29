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

    //Obtiene una lista de empleados con sus lavados

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

    // Guarda un nuevo empleado en la base de datos

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

    // Obtiene un empleado específico por ID

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

    // Actualiza un empleado existente por ID

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

    // Elimina un empleado por ID

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->empleadoService->deleteEmpleado($id);
            
            if (!$result) {
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
     * Restaurar empleado eliminado lógicamente
     * PUT /empleados/{id}/restore
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $result = $this->empleadoService->restoreEmpleado($id);
            
            if (!$result) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Empleado no encontrado en papelera'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Empleado restaurado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al restaurar empleado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener empleados eliminados lógicamente
     * GET /empleados/trashed
     */
    public function trashed(): JsonResponse
    {
        try {
            $empleados = $this->empleadoService->getTrashedEmpleados();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Empleados eliminados obtenidos correctamente',
                'data' => $empleados
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener empleados eliminados: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtiene el conteo de lavados por empleado y día

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

    // Obtiene el conteo de lavados por empleado y semana

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

    // Obtiene el conteo de lavados por empleado y mes

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
