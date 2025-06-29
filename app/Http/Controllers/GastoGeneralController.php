<?php

namespace App\Http\Controllers;

use App\Services\GastoGeneralService;
use App\Http\Requests\GastoGeneral\CreateGastoGeneralRequest;
use App\Http\Requests\GastoGeneral\UpdateGastoGeneralRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GastoGeneralController extends Controller
{
    protected $gastoGeneralService;

    public function __construct(GastoGeneralService $gastoGeneralService)
    {
        $this->gastoGeneralService = $gastoGeneralService;
    }

    /**
     * Display a listing of gastos generales.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Si hay filtros de fecha
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $gastos = $this->gastoGeneralService->getGastosGeneralesByFechaRange(
                    $request->fecha_inicio,
                    $request->fecha_fin
                );
            }
            // Sin filtros
            else {
                $gastos = $this->gastoGeneralService->getAllGastosGenerales();
            }

            return response()->json([
                'status' => 'success',
                'data' => $gastos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los gastos generales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created gasto general.
     */
    public function store(CreateGastoGeneralRequest $request): JsonResponse
    {
        try {
            $gastoGeneral = $this->gastoGeneralService->createGastoGeneral($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Gasto general registrado correctamente',
                'data' => $gastoGeneral
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el gasto general: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified gasto general.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $gastoGeneral = $this->gastoGeneralService->findGastoGeneralById($id);
            
            if (!$gastoGeneral) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gasto general no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $gastoGeneral
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el gasto general: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified gasto general.
     */
    public function update(UpdateGastoGeneralRequest $request, int $id): JsonResponse
    {
        try {
            $gastoGeneral = $this->gastoGeneralService->updateGastoGeneral($id, $request->validated());
            
            if (!$gastoGeneral) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gasto general no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Gasto general actualizado correctamente',
                'data' => $gastoGeneral
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el gasto general: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified gasto general.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->gastoGeneralService->deleteGastoGeneral($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gasto general no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Gasto general eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el gasto general: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted gasto general.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->gastoGeneralService->restoreGastoGeneral($id);
            
            if (!$restored) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gasto general no encontrado en la papelera'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Gasto general restaurado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al restaurar el gasto general: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all soft deleted gastos generales.
     */
    public function trashed(): JsonResponse
    {
        try {
            $gastos = $this->gastoGeneralService->getTrashedGastosGenerales();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Gastos generales eliminados obtenidos correctamente',
                'data' => $gastos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener gastos generales eliminados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get gastos generales metrics.
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->gastoGeneralService->getMetricas();
            
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