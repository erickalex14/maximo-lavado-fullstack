<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Http\Requests\Dashboard\DashboardRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador API para el Dashboard
 * 
 * Proporciona endpoints para obtener datos del dashboard en una SPA Vue.js
 * Aplica principios SOLID:
 * - Single Responsibility: Solo maneja datos del dashboard
 * - Open/Closed: Extensible para nuevos tipos de métricas
 * - Liskov Substitution: Métodos consistentes
 * - Interface Segregation: Endpoints específicos por funcionalidad
 * - Dependency Inversion: Depende de DashboardService (abstracción)
 */
class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Obtener datos principales del dashboard
     * GET /api/dashboard/data
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $data = $this->dashboardService->getDashboardData();
            
            return $this->successResponse($data, 'data', 'Datos del dashboard obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los datos del dashboard', $e);
        }
    }

    /**
     * Obtener métricas principales
     * GET /api/dashboard/metricas
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->dashboardService->getMetricas();
            
            return $this->successResponse($metricas, 'data', 'Métricas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener las métricas', $e);
        }
    }

    /**
     * Obtener actividad reciente
     * GET /api/dashboard/actividad
     */
    public function getActividadReciente(DashboardRequest $request): JsonResponse
    {
        try {
            $limite = $request->validated()['limite'] ?? 5;
            $actividad = $this->dashboardService->getActividadReciente($limite);
            
            return $this->successResponse($actividad, 'data', 'Actividad reciente obtenida exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener la actividad reciente', $e);
        }
    }

    /**
     * Obtener próximas citas
     * GET /api/dashboard/citas
     */
    public function getProximasCitas(DashboardRequest $request): JsonResponse
    {
        try {
            $limite = $request->validated()['limite'] ?? 5;
            $citas = $this->dashboardService->getProximasCitas($limite);
            
            return $this->successResponse($citas, 'data', 'Próximas citas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener las próximas citas', $e);
        }
    }

    /**
     * Obtener datos para gráficos
     * GET /api/dashboard/charts
     */
    public function getChartData(): JsonResponse
    {
        try {
            $chartData = $this->dashboardService->getChartData();
            
            return $this->successResponse($chartData, 'data', 'Datos de gráficos obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener los datos de gráficos', $e);
        }
    }

    /**
     * Obtener alertas del sistema
     * GET /api/dashboard/alertas
     */
    public function getAlertas(): JsonResponse
    {
        try {
            $alertas = $this->dashboardService->getAlertas();
            
            return $this->successResponse($alertas, 'data', 'Alertas obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener las alertas', $e);
        }
    }

    /**
     * Obtener estadísticas generales
     * GET /api/dashboard/estadisticas
     */
    public function getEstadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->dashboardService->getEstadisticasGenerales();
            
            return $this->successResponse($estadisticas, 'data', 'Estadísticas generales obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener las estadísticas', $e);
        }
    }

    /**
     * Obtener análisis financiero
     * GET /api/dashboard/analisis-financiero
     */
    public function getAnalisisFinanciero(): JsonResponse
    {
        try {
            $analisis = $this->dashboardService->getAnalisisFinanciero();
            
            return $this->successResponse($analisis, 'data', 'Análisis financiero obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener el análisis financiero', $e);
        }
    }

    /**
     * Obtener rendimiento operativo
     * GET /api/dashboard/rendimiento-operativo
     */
    public function getRendimientoOperativo(): JsonResponse
    {
        try {
            $rendimiento = $this->dashboardService->getRendimientoOperativo();
            
            return $this->successResponse($rendimiento, 'data', 'Rendimiento operativo obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener el rendimiento operativo', $e);
        }
    }

    /**
     * Obtener resumen completo del dashboard
     * GET /api/dashboard/resumen-completo
     */
    public function getResumenCompleto(): JsonResponse
    {
        try {
            $resumen = $this->dashboardService->getResumenCompleto();
            
            return $this->successResponse($resumen, 'data', 'Resumen completo obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener el resumen completo', $e);
        }
    }

    // =================================================================
    // MÉTODOS PRIVADOS PARA APLICAR DRY
    // =================================================================

    /**
     * Respuesta exitosa estándar
     */
    protected function successResponse($data = null, ?string $dataKey = null, ?string $message = null, int $code = 200): JsonResponse
    {
        return parent::successResponse($data, $dataKey, $message, $code);
    }

        /**
     * Respuesta de error estándar con logging
     */
    protected function errorResponse(string $message, ?\Exception $exception = null, int $code = 500): JsonResponse
    {
        if ($exception) {
            \Log::error($message . ': ' . $exception->getMessage(), [
                'exception' => $exception,
                'trace' => $exception->getTraceAsString()
            ]);
        }
        
        return parent::errorResponse($message, $exception, $code);
    }
}
