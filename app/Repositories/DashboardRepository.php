<?php

namespace App\Repositories;

use App\Contracts\DashboardRepositoryInterface;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\FacturaElectronica;
use App\Models\Lavado;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use App\Models\Ingreso;
use App\Models\Egreso;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getMetricasPrincipales(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();

        // Métricas del día actual
        $ingresosHoy = Ingreso::whereDate('fecha', $today)->sum('monto');
        $egresosHoy = Egreso::whereDate('fecha', $today)->sum('monto');
        $lavadosHoy = Lavado::whereDate('fecha', $today)->count();
        $facturasHoy = FacturaElectronica::whereDate('created_at', $today)->count();

        // Métricas del día anterior para comparación
        $ingresosAyer = Ingreso::whereDate('fecha', $yesterday)->sum('monto');
        $lavadosAyer = Lavado::whereDate('fecha', $yesterday)->count();

        // Cálculo de variaciones
        $variacionIngresos = $ingresosAyer > 0 ? 
            round((($ingresosHoy - $ingresosAyer) / $ingresosAyer) * 100, 1) : 0;
        $variacionLavados = $lavadosAyer > 0 ? 
            round((($lavadosHoy - $lavadosAyer) / $lavadosAyer) * 100, 1) : 0;

        // Métricas generales
        $clientesTotal = Cliente::count();
        $clientesNuevos = Cliente::whereDate('created_at', '>=', $thisMonth)->count();
        $empleadosActivos = Empleado::count();

        return [
            'ingresos_hoy' => $ingresosHoy,
            'egresos_hoy' => $egresosHoy,
            'balance_hoy' => $ingresosHoy - $egresosHoy,
            'lavados_hoy' => $lavadosHoy,
            'facturas_hoy' => $facturasHoy,
            'clientes_total' => $clientesTotal,
            'clientes_nuevos' => $clientesNuevos,
            'empleados_activos' => $empleadosActivos,
            'variaciones' => [
                'ingresos' => $variacionIngresos,
                'lavados' => $variacionLavados
            ]
        ];
    }

    public function getActividadReciente(int $limite = 5): array
    {
        $actividades = collect();

        // Lavados recientes
        try {
            $lavados = Lavado::with(['vehiculo.cliente'])
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($lavado) {
                    return [
                        'id' => $lavado->id,
                        'tipo' => 'lavado',
                        'descripcion' => 'Lavado para ' . ($lavado->vehiculo->cliente->nombre ?? 'Cliente'),
                        'monto' => $lavado->precio,
                        'fecha' => $lavado->created_at,
                        'icono' => 'car-wash'
                    ];
                });
            $actividades = $actividades->merge($lavados);
        } catch (\Exception $e) {
            // En caso de error con relaciones
        }

        // Facturas recientes
        try {
            $facturas = FacturaElectronica::with('venta.cliente')
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($factura) {
                    return [
                        'id' => $factura->factura_electronica_id,
                        'tipo' => 'factura',
                        'descripcion' => 'Factura #' . $factura->numero_factura . ' - ' . ($factura->venta->cliente->nombre ?? 'Cliente'),
                        'monto' => $factura->total,
                        'fecha' => $factura->created_at,
                        'icono' => 'receipt'
                    ];
                });
            $actividades = $actividades->merge($facturas);
        } catch (\Exception $e) {
            // En caso de error con relaciones
        }

        // Nuevos clientes
        $clientes = Cliente::orderBy('created_at', 'desc')
            ->limit($limite)
            ->get()
            ->map(function($cliente) {
                return [
                    'id' => $cliente->id,
                    'tipo' => 'cliente',
                    'descripcion' => 'Nuevo cliente: ' . $cliente->nombre,
                    'monto' => null,
                    'fecha' => $cliente->created_at,
                    'icono' => 'user-plus'
                ];
            });

        $actividades = $actividades->merge($clientes);

        return $actividades
            ->sortByDesc('fecha')
            ->take($limite)
            ->values()
            ->toArray();
    }

    public function getProximasCitas(int $limite = 5): array
    {
        try {
            return Lavado::with(['vehiculo.cliente'])
                ->where('fecha', '>=', Carbon::today())
                ->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->limit($limite)
                ->get()
                ->map(function($lavado) {
                    return [
                        'id' => $lavado->id,
                        'cliente_nombre' => $lavado->vehiculo->cliente->nombre ?? 'Cliente',
                        'vehiculo' => $lavado->vehiculo->marca . ' ' . $lavado->vehiculo->modelo,
                        'fecha' => $lavado->fecha,
                        'hora' => $lavado->hora ?? '09:00',
                        'tipo_lavado' => $lavado->tipo_lavado,
                        'precio' => $lavado->precio
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getChartDataSemanal(): array
    {
        $startDate = Carbon::now()->subDays(6);
        $labels = [];
        $ingresos = [];
        $egresos = [];
        $lavados = [];

        for ($date = $startDate; $date->lte(Carbon::now()); $date->addDay()) {
            $labels[] = $date->format('M d');
            $ingresos[] = Ingreso::whereDate('fecha', $date)->sum('monto');
            $egresos[] = Egreso::whereDate('fecha', $date)->sum('monto');
            $lavados[] = Lavado::whereDate('fecha', $date)->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $ingresos,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true
                ],
                [
                    'label' => 'Egresos',
                    'data' => $egresos,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'fill' => true
                ]
            ],
            'lavados' => [
                'label' => 'Lavados por día',
                'data' => $lavados
            ]
        ];
    }

    public function getAlertasSistema(): array
    {
        $alertas = [];

        // Stock bajo en productos
        $productosStockBajo = ProductoAutomotriz::where('stock', '<=', 5)->count();
        if ($productosStockBajo > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Stock Bajo',
                'mensaje' => "{$productosStockBajo} productos automotrices con stock bajo",
                'fecha' => now(),
                'accion' => 'Ver Inventario'
            ];
        }

        $productosDespensaStockBajo = ProductoDespensa::where('stock', '<=', 5)->count();
        if ($productosDespensaStockBajo > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Stock Bajo Despensa',
                'mensaje' => "{$productosDespensaStockBajo} productos de despensa con stock bajo",
                'fecha' => now(),
                'accion' => 'Ver Inventario'
            ];
        }

        // Lavados pendientes/en proceso
        try {
            $lavadosPendientes = Lavado::where('estado', 'En Proceso')->count();
            if ($lavadosPendientes > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'titulo' => 'Lavados en Proceso',
                    'mensaje' => "{$lavadosPendientes} lavados actualmente en proceso",
                    'fecha' => now(),
                    'accion' => 'Ver Lavados'
                ];
            }
        } catch (\Exception $e) {
            // Si no existe la columna estado, omitir
        }

        // Si no hay alertas, agregar mensaje de estado normal
        if (empty($alertas)) {
            $alertas[] = [
                'tipo' => 'success',
                'titulo' => 'Sistema Operativo',
                'mensaje' => 'Todos los sistemas funcionan correctamente',
                'fecha' => now(),
                'accion' => null
            ];
        }

        return $alertas;
    }

    public function getEstadisticasGenerales(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        return [
            'vehiculos_registrados' => Vehiculo::count(),
            'ingresos_mes_actual' => Ingreso::whereDate('fecha', '>=', $thisMonth)->sum('monto'),
            'ingresos_mes_anterior' => Ingreso::whereBetween('fecha', [$lastMonth, $lastMonthEnd])->sum('monto'),
            'lavados_mes_actual' => Lavado::whereDate('fecha', '>=', $thisMonth)->count(),
            'lavados_mes_anterior' => Lavado::whereBetween('fecha', [$lastMonth, $lastMonthEnd])->count(),
            'facturas_mes_actual' => FacturaElectronica::whereDate('created_at', '>=', $thisMonth)->count(),
            'promedio_lavado' => Lavado::avg('precio') ?? 0
        ];
    }

    public function getIngresosSemanales(): array
    {
        return Ingreso::where('fecha', '>=', Carbon::now()->startOfWeek())
            ->selectRaw('DATE(fecha) as fecha, SUM(monto) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(function ($ingreso) {
                return [
                    'fecha' => $ingreso->fecha,
                    'total' => $ingreso->total
                ];
            })
            ->toArray();
    }

    public function getServiciosPopulares(int $limite = 5): array
    {
        $thisMonth = Carbon::now()->startOfMonth();

        return Lavado::selectRaw('tipo_lavado, COUNT(*) as cantidad, AVG(precio) as precio_promedio')
            ->whereDate('fecha', '>=', $thisMonth)
            ->groupBy('tipo_lavado')
            ->orderBy('cantidad', 'desc')
            ->limit($limite)
            ->get()
            ->map(function ($servicio) {
                return [
                    'nombre' => $servicio->tipo_lavado ?? 'Básico',
                    'cantidad' => $servicio->cantidad,
                    'precio_promedio' => round($servicio->precio_promedio, 2)
                ];
            })
            ->toArray();
    }

    public function getLavadosRecientes(int $limite = 5): array
    {
        try {
            return Lavado::with(['vehiculo.cliente'])
                ->orderBy('created_at', 'desc')
                ->limit($limite)
                ->get()
                ->map(function ($lavado) {
                    return [
                        'id' => $lavado->id,
                        'cliente' => $lavado->vehiculo->cliente->nombre ?? 'Cliente',
                        'vehiculo' => ($lavado->vehiculo->marca ?? '') . ' ' . ($lavado->vehiculo->modelo ?? ''),
                        'tipo_lavado' => $lavado->tipo_lavado ?? 'Básico',
                        'precio' => $lavado->precio,
                        'fecha' => $lavado->created_at->format('Y-m-d H:i')
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getIndicadoresFinancieros(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $totalIngresos = Ingreso::whereDate('fecha', '>=', $thisMonth)->sum('monto');
        $totalEgresos = Egreso::whereDate('fecha', '>=', $thisMonth)->sum('monto');
        $totalFacturas = FacturaElectronica::whereDate('created_at', '>=', $thisMonth)->sum('total');
        $totalLavados = Lavado::whereDate('fecha', '>=', $thisMonth)->sum('precio');

        $ingresosTotales = $totalIngresos + $totalFacturas + $totalLavados;
        $margenGanancia = $ingresosTotales > 0 ? 
            round((($ingresosTotales - $totalEgresos) / $ingresosTotales) * 100, 2) : 0;

        return [
            'ingresos_totales' => $ingresosTotales,
            'egresos_totales' => $totalEgresos,
            'ganancia_neta' => $ingresosTotales - $totalEgresos,
            'margen_ganancia' => $margenGanancia,
            'roi' => $totalEgresos > 0 ? round((($ingresosTotales - $totalEgresos) / $totalEgresos) * 100, 2) : 0
        ];
    }

    public function getResumenVentas(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();

        $ventasAutomotriz = VentaProductoAutomotriz::whereDate('created_at', '>=', $thisMonth)
            ->sum(DB::raw('cantidad * precio_unitario'));
        
        $ventasDespensa = VentaProductoDespensa::whereDate('created_at', '>=', $thisMonth)
            ->sum(DB::raw('cantidad * precio_unitario'));

        return [
            'ventas_productos_automotriz' => $ventasAutomotriz,
            'ventas_productos_despensa' => $ventasDespensa,
            'total_ventas_productos' => $ventasAutomotriz + $ventasDespensa,
            'servicios_lavado' => Lavado::whereDate('fecha', '>=', $thisMonth)->sum('precio'),
            'facturacion_total' => FacturaElectronica::whereDate('created_at', '>=', $thisMonth)->sum('total')
        ];
    }

    public function getRendimientoEmpleados(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();

        try {
            return Empleado::withCount(['lavados' => function ($query) use ($thisMonth) {
                $query->whereDate('fecha', '>=', $thisMonth);
            }])
            ->with(['lavados' => function ($query) use ($thisMonth) {
                $query->whereDate('fecha', '>=', $thisMonth);
            }])
            ->get()
            ->map(function ($empleado) {
                $totalIngresos = $empleado->lavados->sum('precio');
                return [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre,
                    'lavados_realizados' => $empleado->lavados_count,
                    'ingresos_generados' => $totalIngresos,
                    'promedio_por_lavado' => $empleado->lavados_count > 0 ? 
                        round($totalIngresos / $empleado->lavados_count, 2) : 0
                ];
            })
            ->toArray();
        } catch (\Exception $e) {
            return Empleado::all()->map(function ($empleado) {
                return [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre,
                    'lavados_realizados' => 0,
                    'ingresos_generados' => 0,
                    'promedio_por_lavado' => 0
                ];
            })->toArray();
        }
    }
}
