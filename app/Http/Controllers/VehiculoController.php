<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehiculo\CreateVehiculoRequest;
use App\Http\Requests\Vehiculo\UpdateVehiculoRequest;
use App\Services\VehiculoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VehiculoController extends Controller
{
    public function __construct(
        protected VehiculoService $vehiculoService
    ) {}

    /**
     * Obtener lista paginada de vehículos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $filters = $request->only(['search', 'cliente_id', 'tipo']);

            $vehiculos = $this->vehiculoService->getVehiculosPaginated($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $vehiculos,
                'message' => 'Vehículos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener vehículos', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vehículos'
            ], 500);
        }
    }

    /**
     * Obtener todos los vehículos (para selects)
     */
    public function all(): JsonResponse
    {
        try {
            $vehiculos = $this->vehiculoService->getAllVehiculos();

            return response()->json([
                'success' => true,
                'data' => $vehiculos,
                'message' => 'Vehículos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener todos los vehículos', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vehículos'
            ], 500);
        }
    }

    /**
     * Crear nuevo vehículo
     */
    public function store(CreateVehiculoRequest $request): JsonResponse
    {
        try {
            $vehiculo = $this->vehiculoService->createVehiculo($request->validated());

            return response()->json([
                'success' => true,
                'data' => $vehiculo,
                'message' => 'Vehículo creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear vehículo', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtener vehículo específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $vehiculo = $this->vehiculoService->getVehiculoById($id);

            if (!$vehiculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vehículo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $vehiculo,
                'message' => 'Vehículo obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener vehículo', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vehículo'
            ], 500);
        }
    }

    /**
     * Actualizar vehículo
     */
    public function update(UpdateVehiculoRequest $request, int $id): JsonResponse
    {
        try {
            $vehiculo = $this->vehiculoService->updateVehiculo($id, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $vehiculo,
                'message' => 'Vehículo actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar vehículo', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Eliminar vehículo
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->vehiculoService->deleteVehiculo($id);

            return response()->json([
                'success' => true,
                'message' => 'Vehículo eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar vehículo', ['id' => $id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Obtener vehículos por cliente
     */
    public function byCliente(int $clienteId): JsonResponse
    {
        try {
            $vehiculos = $this->vehiculoService->getVehiculosByCliente($clienteId);

            return response()->json([
                'success' => true,
                'data' => $vehiculos,
                'message' => 'Vehículos del cliente obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener vehículos por cliente', ['cliente_id' => $clienteId, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vehículos del cliente'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de vehículos
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->vehiculoService->getEstadisticas();

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de vehículos', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}
