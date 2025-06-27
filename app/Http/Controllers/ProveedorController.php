<?php

namespace App\Http\Controllers;

use App\Services\ProveedorService;
use App\Http\Requests\Proveedor\CreateProveedorRequest;
use App\Http\Requests\Proveedor\UpdateProveedorRequest;
use App\Http\Requests\Proveedor\RegistrarPagoRequest;
use Illuminate\Http\JsonResponse;

class ProveedorController extends Controller
{
    protected $proveedorService;

    public function __construct(ProveedorService $proveedorService)
    {
        $this->proveedorService = $proveedorService;
    }

    /**
     * Display a listing of the resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
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

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
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

    /**
     * Get debt amount for a specific provider.
     */
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

    /**
     * Register a payment for a provider.
     */
    public function registrarPago(RegistrarPagoRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $success = $this->proveedorService->registrarPago($id, $validated['monto'], $validated['descripcion'] ?? null);
            
            if (!$success) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo registrar el pago. Verifique que el proveedor existe y que el monto no exceda la deuda pendiente.'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pago registrado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history for a provider.
     */
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
}
