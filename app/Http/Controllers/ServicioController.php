<?php

namespace App\Http\Controllers;

use App\Services\ServicioService;
use App\Http\Requests\Servicio\CreateServicioRequest;
use App\Http\Requests\Servicio\UpdateServicioRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * 🛠️ ServicioController V2 - Gestión del catálogo unificado de servicios
 * 
 * Maneja servicios con precios dinámicos por tipo de vehículo
 */
class ServicioController extends Controller
{
    protected ServicioService $servicioService;

    public function __construct(ServicioService $servicioService)
    {
        $this->servicioService = $servicioService;
    }

    /**
     * Obtener todos los servicios con paginación
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

            $servicios = $this->servicioService->getAllPaginated($perPage, $filters);

            return response()->json([
                'success' => true,
                'data' => $servicios,
                'filters_applied' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener servicios paginados', [
                'error' => $e->getMessage(),
                'filters' => $filters ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los servicios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los servicios (para selects)
     */
    public function all(): JsonResponse
    {
        try {
            $servicios = $this->servicioService->getAll();

            return response()->json([
                'success' => true,
                'data' => $servicios
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener todos los servicios', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los servicios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener solo servicios activos
     */
    public function activos(): JsonResponse
    {
        try {
            $servicios = $this->servicioService->getActivos();

            return response()->json([
                'success' => true,
                'data' => $servicios
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener servicios activos', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los servicios activos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de servicios
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->servicioService->getEstadisticas();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de servicios', [
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
     * Crear nuevo servicio
     */
    public function store(CreateServicioRequest $request): JsonResponse
    {
        try {
            $servicio = $this->servicioService->crear($request->validated());

            Log::info('✅ Servicio creado exitosamente', [
                'servicio_id' => $servicio->servicio_id,
                'nombre' => $servicio->nombre
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio creado exitosamente',
                'data' => $servicio
            ], 201);
        } catch (\Exception $e) {
            Log::error('❌ Error al crear servicio', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar servicio específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $servicio = $this->servicioService->getById($id);

            if (!$servicio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servicio no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $servicio
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener servicio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar servicio
     */
    public function update(UpdateServicioRequest $request, int $id): JsonResponse
    {
        try {
            $servicio = $this->servicioService->actualizar($id, $request->validated());

            Log::info('✅ Servicio actualizado exitosamente', [
                'servicio_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado exitosamente',
                'data' => $servicio
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar servicio', [
                'id' => $id,
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el servicio',
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
            $servicio = $this->servicioService->toggleActivo($id);

            $estado = $servicio->activo ? 'activado' : 'desactivado';

            Log::info("✅ Servicio {$estado}", [
                'servicio_id' => $id,
                'nuevo_estado' => $servicio->activo
            ]);

            return response()->json([
                'success' => true,
                'message' => "Servicio {$estado} exitosamente",
                'data' => $servicio
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al cambiar estado del servicio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar servicio (soft delete)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $resultado = $this->servicioService->eliminar($id);

            Log::info('✅ Servicio eliminado exitosamente', [
                'servicio_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al eliminar servicio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar servicio eliminado
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $resultado = $this->servicioService->restoreServicio($id);

            Log::info('✅ Servicio restaurado exitosamente', [
                'servicio_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Servicio restaurado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al restaurar servicio', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener servicios eliminados
     */
    public function trashed(): JsonResponse
    {
        try {
            $serviciosEliminados = $this->servicioService->getTrashed();

            return response()->json([
                'success' => true,
                'data' => $serviciosEliminados
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener servicios eliminados', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los servicios eliminados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =========================================================================
    // GESTIÓN DE PRECIOS POR TIPO DE VEHÍCULO
    // =========================================================================

    /**
     * Obtener precios del servicio por tipo de vehículo
     */
    public function getPrecios(int $id): JsonResponse
    {
        try {
            $precios = $this->servicioService->getPreciosPorTipoVehiculo($id);

            return response()->json([
                'success' => true,
                'data' => $precios
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener precios del servicio', [
                'servicio_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los precios del servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar precio del servicio para un tipo de vehículo
     */
    public function updatePrecio(Request $request, int $id, int $tipoVehiculoId): JsonResponse
    {
        $request->validate([
            'precio' => 'required|numeric|min:0'
        ]);

        try {
            $precio = $request->get('precio');
            $resultado = $this->servicioService->updatePrecio($id, $tipoVehiculoId, $precio);

            Log::info('✅ Precio del servicio actualizado', [
                'servicio_id' => $id,
                'tipo_vehiculo_id' => $tipoVehiculoId,
                'precio' => $precio
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado exitosamente',
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar precio del servicio', [
                'servicio_id' => $id,
                'tipo_vehiculo_id' => $tipoVehiculoId,
                'precio' => $request->get('precio'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el precio del servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar precio del servicio para un tipo de vehículo
     */
    public function deletePrecio(int $id, int $tipoVehiculoId): JsonResponse
    {
        try {
            $resultado = $this->servicioService->deletePrecio($id, $tipoVehiculoId);

            Log::info('✅ Precio del servicio eliminado', [
                'servicio_id' => $id,
                'tipo_vehiculo_id' => $tipoVehiculoId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Precio eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al eliminar precio del servicio', [
                'servicio_id' => $id,
                'tipo_vehiculo_id' => $tipoVehiculoId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el precio del servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
