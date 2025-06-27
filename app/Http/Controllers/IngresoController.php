<?php

namespace App\Http\Controllers;

use App\Services\IngresoService;
use App\Http\Requests\Ingreso\CreateIngresoRequest;
use App\Http\Requests\Ingreso\UpdateIngresoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngresoController extends Controller
{
    protected $ingresoService;

    public function __construct(IngresoService $ingresoService)
    {
        $this->ingresoService = $ingresoService;
    }

    /**
     * Display a listing of ingresos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Si hay filtros de fecha
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $ingresos = $this->ingresoService->getIngresosByFechaRange(
                    $request->fecha_inicio,
                    $request->fecha_fin
                );
            }
            // Si hay filtro por tipo
            elseif ($request->has('tipo')) {
                $ingresos = $this->ingresoService->getIngresosByTipo($request->tipo);
            }
            // Sin filtros
            else {
                $ingresos = $this->ingresoService->getAllIngresos();
            }

            return response()->json([
                'status' => 'success',
                'data' => $ingresos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los ingresos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created ingreso.
     */
    public function store(CreateIngresoRequest $request): JsonResponse
    {
        try {
            $ingreso = $this->ingresoService->createIngreso($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Ingreso registrado correctamente',
                'data' => $ingreso
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el ingreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified ingreso.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $ingreso = $this->ingresoService->findIngresoById($id);
            
            if (!$ingreso) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ingreso no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $ingreso
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el ingreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified ingreso.
     */
    public function update(UpdateIngresoRequest $request, int $id): JsonResponse
    {
        try {
            $ingreso = $this->ingresoService->updateIngreso($id, $request->validated());
            
            if (!$ingreso) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ingreso no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ingreso actualizado correctamente',
                'data' => $ingreso
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el ingreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified ingreso.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->ingresoService->deleteIngreso($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ingreso no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ingreso eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el ingreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ingresos metrics.
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->ingresoService->getMetricas();
            
            return response()->json([
                'status' => 'success',
                'data' => $metricas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener métricas: ' . $e->getMessage()
            ], 500);
        }
    }
}
