<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Http\Requests\Dashboard\DashboardRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Mostrar la vista del dashboard
     */
    public function index()
    {
        \Log::info('Dashboard access attempt', [
            'is_authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'guard' => Auth::getDefaultDriver(),
            'session_user_id' => session('user_id'),
            'session_logged_in' => session('logged_in'),
        ]);
        
        // Verificar múltiples formas de autenticación
        $isAuthenticated = Auth::check();
        $hasSessionUserId = session()->has('user_id');
        $sessionLoggedIn = session('logged_in');
        
        \Log::info('Authentication checks', [
            'auth_check' => $isAuthenticated,
            'has_session_user_id' => $hasSessionUserId,
            'session_logged_in' => $sessionLoggedIn
        ]);
        
        if (!$isAuthenticated) {
            // Intentar recuperar la autenticación desde la sesión
            if ($hasSessionUserId && $sessionLoggedIn) {
                $user = \App\Models\User::find(session('user_id'));
                if ($user) {
                    Auth::login($user, true);
                    \Log::info('Authentication recovered from session', [
                        'user_id' => $user->id,
                        'user_email' => $user->email
                    ]);
                    
                    if (Auth::check()) {
                        return view('dashboard');
                    }
                }
            }
            
            \Log::warning('Dashboard access denied - user not authenticated');
            return redirect()->route('login')->with('error', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }
        
        \Log::info('Dashboard access granted', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email
        ]);
        
        return view('dashboard');
    }

    /**
     * Obtener datos principales del dashboard
     */
    public function getData(DashboardRequest $request): JsonResponse
    {
        try {
            $data = $this->dashboardService->getDashboardData();
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Datos del dashboard obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error obteniendo datos del dashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener métricas principales
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->dashboardService->getMetricas();
            
            return response()->json([
                'success' => true,
                'data' => $metricas,
                'message' => 'Métricas obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las métricas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener actividad reciente
     */
    public function getActividadReciente(DashboardRequest $request): JsonResponse
    {
        try {
            $limite = $request->validated()['limite'] ?? 5;
            $actividad = $this->dashboardService->getActividadReciente($limite);
            
            return response()->json([
                'success' => true,
                'data' => $actividad,
                'message' => 'Actividad reciente obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la actividad reciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener próximas citas
     */
    public function getProximasCitas(DashboardRequest $request): JsonResponse
    {
        try {
            $limite = $request->validated()['limite'] ?? 5;
            $citas = $this->dashboardService->getProximasCitas($limite);
            
            return response()->json([
                'success' => true,
                'data' => $citas,
                'message' => 'Próximas citas obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las próximas citas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para gráficos
     */
    public function getChartData(): JsonResponse
    {
        try {
            $chartData = $this->dashboardService->getChartData();
            
            return response()->json([
                'success' => true,
                'data' => $chartData,
                'message' => 'Datos de gráficos obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos de gráficos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener alertas del sistema
     */
    public function getAlertas(): JsonResponse
    {
        try {
            $alertas = $this->dashboardService->getAlertas();
            
            return response()->json([
                'success' => true,
                'data' => $alertas,
                'message' => 'Alertas obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las alertas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas generales
     */
    public function getEstadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->dashboardService->getEstadisticasGenerales();
            
            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas generales obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener análisis financiero
     */
    public function getAnalisisFinanciero(): JsonResponse
    {
        try {
            $analisis = $this->dashboardService->getAnalisisFinanciero();
            
            return response()->json([
                'success' => true,
                'data' => $analisis,
                'message' => 'Análisis financiero obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el análisis financiero: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener rendimiento operativo
     */
    public function getRendimientoOperativo(): JsonResponse
    {
        try {
            $rendimiento = $this->dashboardService->getRendimientoOperativo();
            
            return response()->json([
                'success' => true,
                'data' => $rendimiento,
                'message' => 'Rendimiento operativo obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el rendimiento operativo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen completo del dashboard
     */
    public function getResumenCompleto(): JsonResponse
    {
        try {
            $resumen = $this->dashboardService->getResumenCompleto();
            
            return response()->json([
                'success' => true,
                'data' => $resumen,
                'message' => 'Resumen completo obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen completo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API legacy para compatibilidad (mantener para no romper frontend existente)
     */
    public function apiData(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getDashboardData();
            
            // Formatear para compatibilidad con la API anterior
            $response = [
                'success' => true,
                'data' => [
                    'stats' => [
                        'ingresos_hoy' => $data['metricas']['ingresos_hoy'] ?? 0,
                        'lavados_hoy' => $data['metricas']['lavados_hoy'] ?? 0,
                        'productos_vendidos_hoy' => 0, // Se puede calcular si es necesario
                        'clientes_nuevos' => $data['metricas']['clientes_nuevos'] ?? 0
                    ],
                    'lavados_recientes' => $this->dashboardService->getRendimientoOperativo()['lavados_recientes'] ?? [],
                    'ingresos_semanales' => $this->dashboardService->getAnalisisFinanciero()['ingresos_semanales'] ?? [],
                    'servicios_populares' => $this->dashboardService->getRendimientoOperativo()['servicios_populares'] ?? [],
                    'alertas' => $data['alertas'] ?? []
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Error en dashboard API: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'ingresos_hoy' => 0,
                        'lavados_hoy' => 0,
                        'productos_vendidos_hoy' => 0,
                        'clientes_nuevos' => 0
                    ],
                    'lavados_recientes' => [],
                    'ingresos_semanales' => [],
                    'servicios_populares' => [],
                    'alertas' => [
                        [
                            'tipo' => 'info',
                            'titulo' => 'Sistema Nuevo',
                            'mensaje' => 'No hay datos disponibles. Comienza agregando información.',
                            'fecha' => now()->toISOString()
                        ]
                    ]
                ]
            ]);
        }
    }
}
