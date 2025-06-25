<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Factura;
use App\Models\Lavado;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use App\Models\Ingreso;
use App\Models\Egreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Mostrar la vista del dashboard
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
            'session_data' => session()->all(),
            'cookies' => request()->cookies->all()
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
            
            \Log::warning('Dashboard access denied - user not authenticated', [
                'session_id' => session()->getId(),
                'session_data' => session()->all(),
                'cookies' => request()->cookies->all()
            ]);
            return redirect()->route('login')->with('error', 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }
        
        \Log::info('Dashboard access granted', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email
        ]);
        
        return view('dashboard');
    }

    // Obtener datos para el dashboard
    public function getData()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        // Métricas principales - usando solo count() y sum() simples
        $clientesTotal = Cliente::count();
        $empleados = Empleado::count();
        $lavadosHoy = Lavado::whereDate('created_at', $today)->count();
        $lavadosAyer = Lavado::whereDate('created_at', $yesterday)->count();
        
        // Ingresos del día
        $ingresosHoy = Ingreso::whereDate('created_at', $today)->sum('monto');
        $ingresosAyer = Ingreso::whereDate('created_at', $yesterday)->sum('monto');
        
        // Egresos del día
        $egresosHoy = Egreso::whereDate('created_at', $today)->sum('monto');
        $egresosAyer = Egreso::whereDate('created_at', $yesterday)->sum('monto');
        
        // Calcular variación de ingresos
        $variacionIngresos = $ingresosAyer > 0 ? 
            round((($ingresosHoy - $ingresosAyer) / $ingresosAyer) * 100, 1) : 0;
        
        // Valores simples sin filtros problemáticos
        $lavadosEnProceso = 0; // Sin columna estado
        $clientesNuevos = Cliente::whereDate('created_at', $today)->count();
        $empleadosTrabajando = Empleado::count(); // Todos los empleados
        
        // Actividad reciente simplificada
        $actividadReciente = collect();
        
        // Agregar lavados recientes (sin filtro de estado)
        try {
            $lavadosRecientes = Lavado::with(['vehiculo.cliente'])
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()
                ->map(function($lavado) {
                    return [
                        'id' => $lavado->lavado_id ?? $lavado->id,
                        'tipo' => 'lavado',
                        'descripcion' => 'Lavado realizado para <strong>' . ($lavado->vehiculo->cliente->nombre ?? 'Cliente') . '</strong>',
                        'created_at' => $lavado->created_at
                    ];
                });
            $actividadReciente = $actividadReciente->merge($lavadosRecientes);
        } catch (\Exception $e) {
            // Si hay error con las relaciones, ignorar
        }
        
        // Agregar clientes nuevos
        $clientesNuevosRecientes = Cliente::orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($cliente) {
                return [
                    'id' => $cliente->id,
                    'tipo' => 'cliente',
                    'descripcion' => 'Nuevo cliente: <strong>' . $cliente->nombre . '</strong>',
                    'created_at' => $cliente->created_at
                ];
            });
        
        $actividadReciente = $actividadReciente
            ->merge($clientesNuevosRecientes)
            ->sortByDesc('created_at')
            ->take(5)
            ->values();
        
        // Próximas citas simplificadas
        $proximasCitas = collect();
        try {
            $proximasCitas = Lavado::with(['vehiculo.cliente'])
                ->whereDate('fecha', '>=', $today)
                ->orderBy('fecha', 'asc')
                ->limit(3)
                ->get()
                ->map(function($lavado) {
                    return [
                        'id' => $lavado->lavado_id ?? $lavado->id,
                        'cliente_nombre' => $lavado->vehiculo->cliente->nombre ?? 'Cliente',
                        'hora' => '09:00',
                        'servicio' => $lavado->tipo_lavado ?? 'Lavado',
                        'estado' => 'pendiente'
                    ];
                });
        } catch (\Exception $e) {
            // Si hay error con las relaciones, devolver colección vacía
            $proximasCitas = collect();
        }
          return response()->json([
            'metricas' => [
                'ingresosHoy' => $ingresosHoy ?: 0,
                'egresosHoy' => $egresosHoy ?: 0,
                'lavadosHoy' => $lavadosHoy ?: 0,
                'clientesTotal' => $clientesTotal ?: 0,
                'empleados' => $empleados ?: 0,
                'variacionIngresos' => ($variacionIngresos >= 0 ? '+' : '') . $variacionIngresos . '%',
                'lavadosEnProceso' => $lavadosEnProceso ?: 0,
                'clientesNuevos' => $clientesNuevos ?: 0,
                'empleadosTrabajando' => $empleadosTrabajando ?: 0
            ],
            'actividad_reciente' => $actividadReciente->toArray(),
            'proximas_citas' => $proximasCitas->toArray(),
            'chart_data' => [
                'ingresos' => $this->getWeeklyData('ingresos'),
                'egresos' => $this->getWeeklyData('egresos')
            ]
        ]);
    }

    private function getWeeklyData($tipo)
    {
        $startDate = Carbon::now()->subDays(6);
        $endDate = Carbon::now();
        $data = [];
        
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dia = $date->format('Y-m-d');
            
            if ($tipo === 'ingresos') {
                $monto = Ingreso::whereDate('created_at', $dia)->sum('monto');
            } else {
                $monto = Egreso::whereDate('created_at', $dia)->sum('monto');
            }
            
            $data[] = $monto;
        }
        
        return $data;
    }

    // Obtener datos para gráficos
    public function getChartData()
    {
        $startDate = Carbon::now()->subDays(6);
        $endDate = Carbon::now();

        $ingresosPorDia = [];
        $lavadosPorDia = [];
        
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dia = $date->format('Y-m-d');
            
            $ingresosDia = Ingreso::whereDate('created_at', $dia)->sum('monto');
            $lavadosDia = Lavado::whereDate('created_at', $dia)->count();
            
            $ingresosPorDia[] = [
                'fecha' => $dia,
                'dia' => $date->format('D'),
                'monto' => $ingresosDia
            ];
            
            $lavadosPorDia[] = [
                'fecha' => $dia,
                'dia' => $date->format('D'),
                'cantidad' => $lavadosDia
            ];
        }

        return response()->json([
            'ingresosPorDia' => $ingresosPorDia,
            'lavadosPorDia' => $lavadosPorDia
        ]);
    }
}
