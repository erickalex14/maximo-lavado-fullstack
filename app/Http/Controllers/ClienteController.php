<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cliente\CreateClienteRequest;
use App\Http\Requests\Cliente\UpdateClienteRequest;
use App\Services\ClienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    public function __construct(
        protected ClienteService $clienteService
    ) {}

    /**
     * Obtener lista paginada de clientes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $filters = $request->only(['search', 'activo']);

            $clientes = $this->clienteService->getClientesPaginated($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Clientes obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener clientes', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los clientes (para selects)
     */
    public function all(): JsonResponse
    {
        try {
            $clientes = $this->clienteService->getAllClientes();

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Clientes obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener todos los clientes', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes'
            ], 500);
        }
    }

    /**
     * Crear nuevo cliente
     */
    public function store(CreateClienteRequest $request): JsonResponse
    {
        try {
            $cliente = $this->clienteService->createCliente($request->validated());

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear cliente', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtener cliente específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->getClienteById($id);

            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener cliente', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cliente'
            ], 500);
        }
    }

    /**
     * Actualizar cliente
     */
    public function update(UpdateClienteRequest $request, int $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->updateCliente($id, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Cliente actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar cliente', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Eliminar cliente
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->clienteService->deleteCliente($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar cliente', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar cliente eliminado lógicamente
     * PUT /clientes/{id}/restore
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $result = $this->clienteService->restoreCliente($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado en papelera'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente restaurado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al restaurar cliente', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener clientes eliminados lógicamente
     * GET /clientes/trashed
     */
    public function trashed(): JsonResponse
    {
        try {
            $clientes = $this->clienteService->getTrashedClientes();

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Clientes eliminados obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener clientes eliminados', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes eliminados'
            ], 500);
        }
    }

    /**
     * Buscar clientes
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $term = $request->input('term', '');
            
            if (empty($term)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Término de búsqueda requerido'
                ], 400);
            }

            $clientes = $this->clienteService->searchClientes($term);

            return response()->json([
                'success' => true,
                'data' => $clientes,
                'message' => 'Búsqueda completada'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en búsqueda de clientes', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de clientes
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->clienteService->getEstadisticas();

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de clientes', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Activar/Desactivar cliente
     */
    public function toggleActivo(int $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->toggleActivo($id);

            return response()->json([
                'success' => true,
                'data' => $cliente,
                'message' => 'Estado del cliente actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del cliente', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
