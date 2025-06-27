<?php

namespace App\Http\Controllers;

use App\Services\FacturaService;
use App\Http\Requests\Factura\CreateFacturaRequest;
use App\Http\Requests\Factura\UpdateFacturaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    protected $facturaService;

    public function __construct(FacturaService $facturaService)
    {
        $this->facturaService = $facturaService;
    }

    /**
     * Display a listing of facturas.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Si hay filtros de fecha
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $facturas = $this->facturaService->getFacturasByFechaRange(
                    $request->fecha_inicio,
                    $request->fecha_fin
                );
            }
            // Si hay filtro por cliente
            elseif ($request->has('cliente_id')) {
                $facturas = $this->facturaService->getFacturasByClienteId($request->cliente_id);
            }
            // Sin filtros
            else {
                $facturas = $this->facturaService->getAllFacturas();
            }

            return response()->json([
                'status' => 'success',
                'data' => $facturas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las facturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created factura.
     */
    public function store(CreateFacturaRequest $request): JsonResponse
    {
        try {
            $factura = $this->facturaService->createFactura($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Factura creada correctamente',
                'data' => $factura
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified factura.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $factura = $this->facturaService->findFacturaById($id);
            
            if (!$factura) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $factura
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified factura.
     */
    public function update(UpdateFacturaRequest $request, int $id): JsonResponse
    {
        try {
            $factura = $this->facturaService->updateFactura($id, $request->validated());
            
            if (!$factura) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Factura actualizada correctamente',
                'data' => $factura
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified factura.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->facturaService->deleteFactura($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Factura eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find factura by numero.
     */
    public function findByNumero(string $numeroFactura): JsonResponse
    {
        try {
            $factura = $this->facturaService->findByNumeroFactura($numeroFactura);
            
            if (!$factura) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $factura
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al buscar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get facturas metrics.
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->facturaService->getMetricas();
            
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
