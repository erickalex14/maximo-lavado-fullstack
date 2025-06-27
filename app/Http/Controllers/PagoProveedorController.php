<?php

namespace App\Http\Controllers;

use App\Services\PagoProveedorService;
use App\Http\Requests\PagoProveedor\CreatePagoProveedorRequest;
use App\Http\Requests\PagoProveedor\UpdatePagoProveedorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PagoProveedorController extends Controller
{
    protected $pagoProveedorService;

    public function __construct(PagoProveedorService $pagoProveedorService)
    {
        $this->pagoProveedorService = $pagoProveedorService;
    }

    /**
     * Display a listing of pagos a proveedores.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Si hay filtros de fecha
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $pagos = $this->pagoProveedorService->getPagosByFechaRange(
                    $request->fecha_inicio,
                    $request->fecha_fin
                );
            }
            // Si hay filtro por proveedor
            elseif ($request->has('proveedor_id')) {
                $pagos = $this->pagoProveedorService->getPagosByProveedorId($request->proveedor_id);
            }
            // Sin filtros
            else {
                $pagos = $this->pagoProveedorService->getAllPagosProveedores();
            }

            return response()->json([
                'status' => 'success',
                'data' => $pagos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los pagos a proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pago proveedor.
     */
    public function store(CreatePagoProveedorRequest $request): JsonResponse
    {
        try {
            $pagoProveedor = $this->pagoProveedorService->createPagoProveedor($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Pago a proveedor registrado correctamente',
                'data' => $pagoProveedor
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el pago a proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pago proveedor.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $pagoProveedor = $this->pagoProveedorService->findPagoProveedorById($id);
            
            if (!$pagoProveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pago a proveedor no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $pagoProveedor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el pago a proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified pago proveedor.
     */
    public function update(UpdatePagoProveedorRequest $request, int $id): JsonResponse
    {
        try {
            $pagoProveedor = $this->pagoProveedorService->updatePagoProveedor($id, $request->validated());
            
            if (!$pagoProveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pago a proveedor no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pago a proveedor actualizado correctamente',
                'data' => $pagoProveedor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el pago a proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pago proveedor.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->pagoProveedorService->deletePagoProveedor($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pago a proveedor no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pago a proveedor eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el pago a proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pagos proveedores metrics.
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->pagoProveedorService->getMetricas();
            
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
