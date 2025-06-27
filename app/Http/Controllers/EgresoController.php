<?php

namespace App\Http\Controllers;

use App\Services\EgresoService;
use App\Http\Requests\Egreso\CreateEgresoRequest;
use App\Http\Requests\Egreso\UpdateEgresoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EgresoController extends Controller
{
    protected $egresoService;

    public function __construct(EgresoService $egresoService)
    {
        $this->egresoService = $egresoService;
    }

    /**
     * Display a listing of egresos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Si hay filtros de fecha
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $egresos = $this->egresoService->getEgresosByFechaRange(
                    $request->fecha_inicio,
                    $request->fecha_fin
                );
            }
            // Si hay filtro por tipo
            elseif ($request->has('tipo')) {
                $egresos = $this->egresoService->getEgresosByTipo($request->tipo);
            }
            // Sin filtros
            else {
                $egresos = $this->egresoService->getAllEgresos();
            }

            return response()->json([
                'status' => 'success',
                'data' => $egresos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los egresos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created egreso.
     */
    public function store(CreateEgresoRequest $request): JsonResponse
    {
        try {
            $egreso = $this->egresoService->createEgreso($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Egreso registrado correctamente',
                'data' => $egreso
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el egreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified egreso.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $egreso = $this->egresoService->findEgresoById($id);
            
            if (!$egreso) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Egreso no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $egreso
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el egreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified egreso.
     */
    public function update(UpdateEgresoRequest $request, int $id): JsonResponse
    {
        try {
            $egreso = $this->egresoService->updateEgreso($id, $request->validated());
            
            if (!$egreso) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Egreso no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Egreso actualizado correctamente',
                'data' => $egreso
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el egreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified egreso.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->egresoService->deleteEgreso($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Egreso no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Egreso eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el egreso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get egresos metrics.
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->egresoService->getMetricas();
            
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
