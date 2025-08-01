<?php

namespace App\Http\Controllers;

use App\Services\TipoVehiculoService;
use App\Http\Requests\TipoVehiculo\CreateTipoVehiculoRequest;
use App\Http\Requests\TipoVehiculo\UpdateTipoVehiculoRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * 🚗 TipoVehiculoController V2 - Gestión dinámica de tipos de vehículo
 * 
 * Maneja la gestión completa de tipos de vehículo del sistema unificado
 */
class TipoVehiculoController extends Controller
{
    protected TipoVehiculoService $tipoVehiculoService;

    public function __construct(TipoVehiculoService $tipoVehiculoService)
    {
        $this->tipoVehiculoService = $tipoVehiculoService;
    }

    /**
     * Obtener todos los tipos de vehículo con paginación
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $activo = $request->get('activo', null);

            $filters = array_filter([
                'search' => $search,
                'activo' => $activo,
            ]);

            $tiposVehiculos = $this->tipoVehiculoService->getAllPaginated($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $tiposVehiculos,
                'filters_applied' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de vehículo paginados', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los tipos de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los tipos de vehículo (para selects)
     */
    public function all(): JsonResponse
    {
        try {
            $tiposVehiculos = $this->tipoVehiculoService->getAll();

            return response()->json([
                'success' => true,
                'data' => $tiposVehiculos
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener todos los tipos de vehículo', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los tipos de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de tipos de vehículo
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->tipoVehiculoService->getEstadisticas();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de tipos de vehículo', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo tipo de vehículo
     */
    public function store(CreateTipoVehiculoRequest $request): JsonResponse
    {
        try {
            $tipoVehiculo = $this->tipoVehiculoService->crear($request->validated());

            Log::info('✅ Tipo de vehículo creado exitosamente', [
                'tipo_vehiculo_id' => $tipoVehiculo->tipo_vehiculo_id,
                'nombre' => $tipoVehiculo->nombre
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de vehículo creado exitosamente',
                'data' => $tipoVehiculo
            ], 201);
        } catch (\Exception $e) {
            Log::error('❌ Error al crear tipo de vehículo', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar tipo de vehículo específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $tipoVehiculo = $this->tipoVehiculoService->getById($id);

            if (!$tipoVehiculo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de vehículo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $tipoVehiculo
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipo de vehículo', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar tipo de vehículo
     */
    public function update(UpdateTipoVehiculoRequest $request, int $id): JsonResponse
    {
        try {
            $tipoVehiculo = $this->tipoVehiculoService->actualizar($id, $request->validated());

            Log::info('✅ Tipo de vehículo actualizado exitosamente', [
                'tipo_vehiculo_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de vehículo actualizado exitosamente',
                'data' => $tipoVehiculo
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar tipo de vehículo', [
                'id' => $id,
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternar estado activo/inactivo
     */
    public function toggleActivo(int $id): JsonResponse
    {
        try {
            $tipoVehiculo = $this->tipoVehiculoService->toggleActivo($id);

            $estado = $tipoVehiculo->activo ? 'activado' : 'desactivado';

            Log::info("✅ Tipo de vehículo {$estado}", [
                'tipo_vehiculo_id' => $id,
                'nuevo_estado' => $tipoVehiculo->activo
            ]);

            return response()->json([
                'success' => true,
                'message' => "Tipo de vehículo {$estado} exitosamente",
                'data' => $tipoVehiculo
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado del tipo de vehículo', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar tipo de vehículo (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $resultado = $this->tipoVehiculoService->eliminar($id);

            Log::info('✅ Tipo de vehículo eliminado exitosamente', [
                'tipo_vehiculo_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de vehículo eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al eliminar tipo de vehículo', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar tipo de vehículo eliminado
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $resultado = $this->tipoVehiculoService->restaurar($id);

            Log::info('✅ Tipo de vehículo restaurado exitosamente', [
                'tipo_vehiculo_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tipo de vehículo restaurado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al restaurar tipo de vehículo', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el tipo de vehículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipos de vehículo eliminados
     */
    public function trashed(): JsonResponse
    {
        try {
            $tiposEliminados = $this->tipoVehiculoService->getTrashed();

            return response()->json([
                'success' => true,
                'data' => $tiposEliminados
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tipos de vehículo eliminados', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los tipos de vehículo eliminados',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
