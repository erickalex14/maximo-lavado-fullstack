<?php

namespace App\Http\Controllers;

use App\Services\VentaService;
use App\Http\Requests\Venta\CreateVentaRequest;
use App\Http\Requests\Venta\UpdateVentaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 🚗 VentaController V2 - Sistema unificado de ventas con flujo automático
 * 
 * FLUJOS AUTOMATIZADOS:
 * 1. Solo productos: Venta → FacturaElectronica → Stock update → Ingreso
 * 2. Solo servicios: Venta → FacturaElectronica → Ingreso → Lavado
 * 3. Mixto: Venta → FacturaElectronica → Stock update → Ingreso → Lavado
 * 
 * Aplica principios SOLID:
 * - Single Responsibility: Solo maneja ventas con flujos automáticos
 * - Open/Closed: Extensible para nuevos tipos de venta
 * - Liskov Substitution: Usa interfaces consistentes
 * - Interface Segregation: Métodos específicos por tipo
 * - Dependency Inversion: Depende de VentaService (abstracción)
 */
class VentaController extends Controller
{
    protected VentaService $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    // =======================================================
    // ENDPOINTS GENERALES DE VENTAS
    // =======================================================

    /**
     * Obtener todas las ventas con filtros opcionales
     * GET /ventas
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $ventas = $this->getVentasWithFilters($request);

            return $this->successResponse($ventas, 'ventas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener las ventas', $e);
        }
    }

    /**
     * Obtener métricas generales de ventas
     * GET /ventas/metricas
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->ventaService->getMetricas();
            
            return response()->json($metricas);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener métricas', $e);
        }
    }

    /**
     * Obtener productos disponibles para venta
     * GET /ventas/productos-disponibles
     */
    public function getProductosDisponibles(): JsonResponse
    {
        try {
            $productos = $this->ventaService->getProductosDisponibles();
            
            return response()->json($productos);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener productos', $e);
        }
    }

    /**
     * Obtener clientes para ventas
     * GET /ventas/clientes
     */
    public function getClientes(): JsonResponse
    {
        try {
            $clientes = $this->ventaService->getClientes();
            
            return $this->successResponse($clientes, 'clientes');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener clientes', $e);
        }
    }

    // =======================================================
    // ENDPOINTS ESPECÍFICOS PARA VENTAS AUTOMOTRICES
    // =======================================================

    /**
     * Obtener ventas de productos automotrices
     * GET /ventas/automotrices
     */
    public function getVentasAutomotrices(Request $request): JsonResponse
    {
        try {
            $ventas = $this->getVentasAutomotricesWithFilters($request);

            return $this->successResponse($ventas, 'data');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener ventas automotrices', $e);
        }
    }

    /**
     * Crear nueva venta (UNIFICADO - maneja productos, servicios y mixtas)
     * POST /ventas
     */
    public function store(CreateVentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->crearVentaCompleta(
                $request->only(['cliente_id', 'empleado_id', 'fecha', 'metodo_pago', 'observaciones']),
                $request->input('detalles', [])
            );
            
            return $this->successResponse($venta, 'venta', 'Venta creada correctamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear la venta', $e);
        }
    }

    /**
     * Actualizar venta existente
     * PUT /ventas/{id}
     */
    public function update(int $id, UpdateVentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->actualizarVentaCompleta(
                $id,
                $request->only(['cliente_id', 'empleado_id', 'fecha', 'metodo_pago', 'observaciones']),
                $request->input('detalles', [])
            );
            
            return $this->successResponse($venta, 'venta', 'Venta actualizada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la venta', $e);
        }
    }

    /**
     * Obtener una venta específica
     * GET /ventas/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->getById($id);
            
            if (!$venta) {
                return $this->notFoundResponse('Venta no encontrada');
            }

            return $this->successResponse($venta, 'venta');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener la venta', $e);
        }
    }

    /**
     * Eliminar venta (soft delete)
     * DELETE /ventas/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->ventaService->eliminarVenta($id);
            
            if (!$result) {
                return $this->notFoundResponse('Venta no encontrada');
            }

            return $this->successResponse(null, null, 'Venta eliminada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar la venta', $e);
        }
    }

    // =======================================================
    // MÉTODOS DE CONSULTA Y ESTADÍSTICAS  
    // =======================================================

    /**
     * Obtener una venta automotriz específica
     * GET /ventas/automotrices/{id}
     */
    public function getVentaAutomotriz(int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->getVentaAutomotrizById($id);
            
            if (!$venta) {
                return $this->notFoundResponse('Venta no encontrada');
            }

            return $this->successResponse($venta, 'data');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener la venta', $e);
        }
    }

    /**
     * Actualizar una venta automotriz
     * PUT /ventas/automotrices/{id}
     */
    public function updateVentaAutomotriz(UpdateVentaProductoAutomotrizRequest $request, int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->updateVentaAutomotriz($id, $request->validated());
            
            if (!$venta) {
                return $this->notFoundResponse('Venta no encontrada');
            }

            return $this->successResponse($venta, 'data', 'Venta actualizada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la venta', $e);
        }
    }

    /**
     * Eliminar una venta automotriz
     * DELETE /ventas/automotrices/{id}
     */
    public function deleteVentaAutomotriz(int $id): JsonResponse
    {
        try {
            $result = $this->ventaService->deleteVentaAutomotriz($id);
            
            if (!$result) {
                return $this->notFoundResponse('Venta automotriz no encontrada');
            }

            return $this->successResponse(null, null, 'Venta automotriz eliminada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar la venta automotriz', $e);
        }
    }

    /**
     * Restaurar venta automotriz eliminada lógicamente
     * PUT /ventas/automotrices/{id}/restore
     */
    public function restoreVentaAutomotriz(int $id): JsonResponse
    {
        try {
            $result = $this->ventaService->restoreVentaAutomotriz($id);
            
            if (!$result) {
                return $this->notFoundResponse('Venta automotriz no encontrada en papelera');
            }

            return $this->successResponse(null, null, 'Venta automotriz restaurada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al restaurar la venta automotriz', $e);
        }
    }

    /**
     * Obtener ventas automotrices eliminadas lógicamente
     * GET /ventas/automotrices/trashed
     */
    public function getTrashedVentasAutomotrices(): JsonResponse
    {
        try {
            $ventas = $this->ventaService->getTrashedVentasAutomotrices();
            
            return $this->successResponse($ventas, 'ventas_automotrices_eliminadas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener ventas automotrices eliminadas', $e);
        }
    }

    /**
     * Obtener métricas de ventas automotrices
     * GET /ventas/automotrices/metricas
     */
    public function getMetricasAutomotrices(Request $request): JsonResponse
    {
        try {
            $metricas = $this->ventaService->getMetricasAutomotrices($request->all());
            
            return $this->successResponse($metricas, 'data');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener métricas', $e);
        }
    }

    // =======================================================
    // ENDPOINTS ESPECÍFICOS PARA VENTAS DE DESPENSA
    // =======================================================

    /**
     * Obtener ventas de productos de despensa
     * GET /ventas/despensa
     */
    public function getVentasDespensa(Request $request): JsonResponse
    {
        try {
            $ventas = $this->getVentasDespensaWithFilters($request);

            return $this->successResponse($ventas, 'data');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener ventas de despensa', $e);
        }
    }

    /**
     * Crear una venta de producto de despensa
     * POST /ventas/despensa
     */
    public function createVentaDespensa(CreateVentaProductoDespensaRequest $request): JsonResponse
    {
        try {
            $result = $this->ventaService->createVentaDespensa($request->validated());
            
            return $this->successResponse($result, 'data', 'Venta de producto de despensa registrada correctamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al registrar la venta', $e);
        }
    }

    /**
     * Obtener una venta de despensa específica
     * GET /ventas/despensa/{id}
     */
    public function getVentaDespensa(int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->getVentaDespensaById($id);
            
            if (!$venta) {
                return $this->notFoundResponse('Venta no encontrada');
            }

            return $this->successResponse($venta, 'data');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener la venta', $e);
        }
    }

    /**
     * Actualizar una venta de despensa
     * PUT /ventas/despensa/{id}
     */
    public function updateVentaDespensa(UpdateVentaProductoDespensaRequest $request, int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->updateVentaDespensa($id, $request->validated());
            
            if (!$venta) {
                return $this->notFoundResponse('Venta no encontrada');
            }

            return $this->successResponse($venta, 'data', 'Venta actualizada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la venta', $e);
        }
    }

    /**
     * Eliminar una venta de despensa
     * DELETE /ventas/despensa/{id}
     */
    public function deleteVentaDespensa(int $id): JsonResponse
    {
        try {
            $result = $this->ventaService->deleteVentaDespensa($id);
            
            if (!$result) {
                return $this->notFoundResponse('Venta de despensa no encontrada');
            }

            return $this->successResponse(null, null, 'Venta de despensa eliminada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar la venta de despensa', $e);
        }
    }

    /**
     * Restaurar venta de despensa eliminada lógicamente
     * PUT /ventas/despensa/{id}/restore
     */
    public function restoreVentaDespensa(int $id): JsonResponse
    {
        try {
            $result = $this->ventaService->restoreVentaDespensa($id);
            
            if (!$result) {
                return $this->notFoundResponse('Venta de despensa no encontrada en papelera');
            }

            return $this->successResponse(null, null, 'Venta de despensa restaurada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al restaurar la venta de despensa', $e);
        }
    }

    /**
     * Obtener ventas de despensa eliminadas lógicamente
     * GET /ventas/despensa/trashed
     */
    public function getTrashedVentasDespensa(): JsonResponse
    {
        try {
            $ventas = $this->ventaService->getTrashedVentasDespensa();
            
            return $this->successResponse($ventas, 'ventas_despensa_eliminadas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener ventas de despensa eliminadas', $e);
        }
    }

    /**
     * Obtener todas las ventas eliminadas lógicamente
     * GET /ventas/trashed
     */
    public function trashed(): JsonResponse
    {
        try {
            $ventas = $this->ventaService->getTrashedVentas();
            
            return $this->successResponse($ventas, 'ventas_eliminadas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener ventas eliminadas', $e);
        }
    }

    // =======================================================
    // MÉTODOS PRIVADOS PARA APLICAR DRY
    // =======================================================

    /**
     * Obtener ventas con filtros aplicados
     */
    private function getVentasWithFilters(Request $request)
    {
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            return $this->ventaService->getVentasByFechaRange(
                $request->fecha_inicio,
                $request->fecha_fin
            );
        }

        if ($request->has('cliente_id')) {
            return $this->ventaService->getVentasByClienteId($request->cliente_id);
        }

        return $this->ventaService->getAllVentas();
    }

    /**
     * Obtener ventas automotrices con filtros aplicados
     */
    private function getVentasAutomotricesWithFilters(Request $request)
    {
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            return $this->ventaService->getVentasAutomotricesByFechaRange(
                $request->fecha_inicio,
                $request->fecha_fin
            );
        }

        return $this->ventaService->getAllVentasAutomotrices();
    }

    /**
     * Obtener ventas de despensa con filtros aplicados
     */
    private function getVentasDespensaWithFilters(Request $request)
    {
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            return $this->ventaService->getVentasDespensaByFechaRange(
                $request->fecha_inicio,
                $request->fecha_fin
            );
        }

        return $this->ventaService->getAllVentasDespensa();
    }

    /**
     * Respuesta exitosa estándar
     */
    private function successResponse($data, ?string $dataKey = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $response = ['status' => 'success'];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            if ($dataKey) {
                $response[$dataKey] = $data;
            } else {
                $response = array_merge($response, $data);
            }
        }

        return response()->json($response, $status);
    }

    /**
     * Respuesta de error estándar
     */
    private function errorResponse(string $message, \Exception $e, int $status = 500): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message . ': ' . $e->getMessage()
        ], $status);
    }

    /**
     * Respuesta para recursos no encontrados
     */
    private function notFoundResponse(string $message): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], 404);
    }
}
