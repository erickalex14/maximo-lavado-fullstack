<?php

namespace App\Http\Controllers;

use App\Services\ProveedorService;
use App\Http\Requests\Proveedor\CreateProveedorRequest;
use App\Http\Requests\Proveedor\UpdateProveedorRequest;

use App\Http\Requests\Proveedor\CreatePagoRequest;
use App\Http\Requests\Proveedor\UpdatePagoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    protected $proveedorService;

    public function __construct(ProveedorService $proveedorService)
    {
        $this->proveedorService = $proveedorService;
    }

    // Muestra todos los proveedores

    public function index(): JsonResponse
    {
        try {
            $proveedores = $this->proveedorService->getAllProveedores();
            
            if ($proveedores->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay proveedores registrados'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Proveedores encontrados',
                'data' => $proveedores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    // Guarda un nuevo proveedor

    public function store(CreateProveedorRequest $request): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->createProveedor($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Proveedor creado correctamente',
                'data' => $proveedor
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    // Muestra un proveedor específico por ID

    public function show(int $id): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->findProveedorById($id);
            
            if (!$proveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $proveedor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    // Actualiza un proveedor específico por ID

    public function update(UpdateProveedorRequest $request, int $id): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->updateProveedor($id, $request->validated());
            
            if (!$proveedor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Proveedor actualizado correctamente',
                'data' => $proveedor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    // Elimina un proveedor específico por ID

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->proveedorService->deleteProveedor($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Proveedor eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    // Restaura un proveedor eliminado lógicamente

    public function restore(int $id): JsonResponse
    {
        try {
            $restored = $this->proveedorService->restoreProveedor($id);
            
            if (!$restored) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado en la papelera'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Proveedor restaurado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al restaurar el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtiene todos los proveedores eliminados lógicamente

    public function trashed(): JsonResponse
    {
        try {
            $proveedores = $this->proveedorService->getTrashedProveedores();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Proveedores eliminados obtenidos correctamente',
                'data' => $proveedores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los proveedores eliminados: ' . $e->getMessage()
            ], 500);
        }
    }

    // Ver deuda pendiente de un proveedor

    public function verDeuda(int $id): JsonResponse
    {
        try {
            $deuda = $this->proveedorService->getDeudaProveedor($id);
            
            if ($deuda === null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proveedor no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => ['deuda_pendiente' => $deuda]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener la deuda: ' . $e->getMessage()
            ], 500);
        }
    }



    // Ver historial de pagos de un proveedor

    public function pagos(int $id): JsonResponse
    {
        try {
            $pagos = $this->proveedorService->getPagosProveedor($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Historial de pagos encontrado',
                'data' => $pagos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el historial de pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    // =======================================================
    // MÉTODOS CONSOLIDADOS PARA GESTIÓN COMPLETA DE PAGOS
    // =======================================================

    // Obtener todos los pagos de proveedores

    public function getAllPagos(Request $request): JsonResponse
    {
        try {
            // Si hay filtros de fecha
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $pagos = $this->proveedorService->getPagosByFechaRange(
                    $request->fecha_inicio,
                    $request->fecha_fin
                );
            }
            // Sin filtros - todos los pagos
            else {
                $pagos = $this->proveedorService->getAllPagos();
            }

            return response()->json([
                'status' => 'success',
                'data' => $pagos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    //Crear un pago con transacción completa

    public function createPago(CreatePagoRequest $request): JsonResponse
    {
        try {
            $result = $this->proveedorService->createPago($request->validated());
            
            if (!$result['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pago registrado correctamente',
                'data' => $result['data']
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtener un pago específico por ID

    public function getPago(int $pagoId): JsonResponse
    {
        try {
            $pago = $this->proveedorService->getPagoById($pagoId);
            
            if (!$pago) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $pago
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    // Actualizar un pago específico

    public function updatePago(UpdatePagoRequest $request, int $pagoId): JsonResponse
    {
        try {
            $pago = $this->proveedorService->updatePago($pagoId, $request->validated());
            
            if (!$pago) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pago actualizado correctamente',
                'data' => $pago
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    // Eliminar un pago específico

    public function deletePago(int $pagoId): JsonResponse
    {
        try {
            $deleted = $this->proveedorService->deletePago($pagoId);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pago eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtener métricas de pagos
    
    public function getMetricasPagos(Request $request): JsonResponse
    {
        try {
            $metricas = $this->proveedorService->getMetricasPagos($request->all());
            
            return response()->json([
                'status' => 'success',
                'data' => $metricas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las métricas: ' . $e->getMessage()
            ], 500);
        }
    }
}
