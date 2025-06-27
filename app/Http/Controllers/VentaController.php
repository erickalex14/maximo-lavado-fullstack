<?php

namespace App\Http\Controllers;

use App\Services\VentaService;
use App\Http\Requests\Venta\CreateVentaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    protected $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    /**
     * Display a listing of ventas.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Si hay filtros de fecha
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $ventas = $this->ventaService->getVentasByFechaRange(
                    $request->fecha_inicio,
                    $request->fecha_fin
                );
            }
            // Si hay filtro por cliente
            elseif ($request->has('cliente_id')) {
                $ventas = $this->ventaService->getVentasByClienteId($request->cliente_id);
            }
            // Sin filtros
            else {
                $ventas = $this->ventaService->getAllVentas();
            }

            return response()->json([
                'status' => 'success',
                'ventas' => $ventas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created venta.
     */
    public function store(CreateVentaRequest $request): JsonResponse
    {
        try {
            $resultado = $this->ventaService->procesarVenta($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Venta registrada exitosamente',
                'ventas' => $resultado['ventas'],
                'monto_total' => $resultado['monto_total']
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get ventas metrics.
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->ventaService->getMetricas();
            
            return response()->json($metricas);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener métricas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get productos disponibles para venta.
     */
    public function getProductosDisponibles(): JsonResponse
    {
        try {
            $productos = $this->ventaService->getProductosDisponibles();
            
            return response()->json($productos);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clientes para ventas.
     */
    public function getClientes(): JsonResponse
    {
        try {
            $clientes = $this->ventaService->getClientes();
            
            return response()->json([
                'status' => 'success',
                'clientes' => $clientes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener clientes: ' . $e->getMessage()
            ], 500);
        }
    }
}
