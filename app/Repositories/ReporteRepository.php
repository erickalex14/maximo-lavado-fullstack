<?php

namespace App\Repositories;

use App\Contracts\ReporteRepositoryInterface;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use App\Models\Lavado;
use App\Models\Ingreso;
use App\Models\Egreso;
use App\Models\Factura;
use App\Models\Empleado;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\Cliente;
use App\Models\GastoGeneral;
use App\Models\PagoProveedor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteRepository implements ReporteRepositoryInterface
{
    public function getReporteVentas(string $fechaInicio, string $fechaFin): array
    {
        $ventasAutomotrices = VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->map(function($venta) {
                return [
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                    'producto' => $venta->productoAutomotriz->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'Automotriz'
                ];
            });

        $ventasDespensa = VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->map(function($venta) {
                return [
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                    'producto' => $venta->productoDespensa->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'Despensa'
                ];
            });

        $todasVentas = $ventasAutomotrices->merge($ventasDespensa)->sortBy('fecha')->values();
        
        $totalVentas = $todasVentas->sum('total');
        $cantidadVentas = $todasVentas->count();
        $promedioVenta = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;

        return [
            'ventas' => $todasVentas,
            'resumen' => [
                'total_ventas' => $totalVentas,
                'cantidad_ventas' => $cantidadVentas,
                'promedio_venta' => $promedioVenta,
                'ventas_automotriz' => $ventasAutomotrices->sum('total'),
                'ventas_despensa' => $ventasDespensa->sum('total'),
            ]
        ];
    }

    public function getReporteLavados(string $fechaInicio, string $fechaFin): array
    {
        $lavados = Lavado::with(['cliente', 'vehiculo', 'empleado'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->map(function($lavado) {
                return [
                    'fecha' => $lavado->fecha,
                    'cliente' => $lavado->cliente->nombre,
                    'vehiculo' => $lavado->vehiculo->placa . ' - ' . $lavado->vehiculo->marca . ' ' . $lavado->vehiculo->modelo,
                    'empleado' => $lavado->empleado->nombre,
                    'tipo_lavado' => $lavado->tipo_lavado,
                    'precio' => $lavado->precio,
                    'estado' => $lavado->estado
                ];
            });

        $totalIngresos = $lavados->sum('precio');
        $cantidadLavados = $lavados->count();
        $promedioLavado = $cantidadLavados > 0 ? $totalIngresos / $cantidadLavados : 0;

        // Lavados por empleado
        $lavadosPorEmpleado = $lavados->groupBy('empleado')->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum('precio')
            ];
        });

        // Lavados por tipo
        $lavadosPorTipo = $lavados->groupBy('tipo_lavado')->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum('precio')
            ];
        });

        return [
            'lavados' => $lavados,
            'resumen' => [
                'total_ingresos' => $totalIngresos,
                'cantidad_lavados' => $cantidadLavados,
                'promedio_lavado' => $promedioLavado,
                'lavados_por_empleado' => $lavadosPorEmpleado,
                'lavados_por_tipo' => $lavadosPorTipo
            ]
        ];
    }

    public function getReporteIngresos(string $fechaInicio, string $fechaFin): array
    {
        $ingresos = Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha')
            ->get()
            ->map(function($ingreso) {
                return [
                    'fecha' => $ingreso->fecha,
                    'descripcion' => $ingreso->descripcion,
                    'monto' => $ingreso->monto,
                    'categoria' => $ingreso->categoria ?? 'General'
                ];
            });

        $totalIngresos = $ingresos->sum('monto');
        $cantidadIngresos = $ingresos->count();
        $promedioIngreso = $cantidadIngresos > 0 ? $totalIngresos / $cantidadIngresos : 0;

        // Ingresos por categoría
        $ingresosPorCategoria = $ingresos->groupBy('categoria')->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum('monto')
            ];
        });

        return [
            'ingresos' => $ingresos,
            'resumen' => [
                'total_ingresos' => $totalIngresos,
                'cantidad_ingresos' => $cantidadIngresos,
                'promedio_ingreso' => $promedioIngreso,
                'ingresos_por_categoria' => $ingresosPorCategoria
            ]
        ];
    }

    public function getReporteEgresos(string $fechaInicio, string $fechaFin): array
    {
        $egresos = Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha')
            ->get()
            ->map(function($egreso) {
                return [
                    'fecha' => $egreso->fecha,
                    'descripcion' => $egreso->descripcion,
                    'monto' => $egreso->monto,
                    'categoria' => $egreso->categoria ?? 'General'
                ];
            });

        $totalEgresos = $egresos->sum('monto');
        $cantidadEgresos = $egresos->count();
        $promedioEgreso = $cantidadEgresos > 0 ? $totalEgresos / $cantidadEgresos : 0;

        // Egresos por categoría
        $egresosPorCategoria = $egresos->groupBy('categoria')->map(function($grupo) {
            return [
                'cantidad' => $grupo->count(),
                'total' => $grupo->sum('monto')
            ];
        });

        return [
            'egresos' => $egresos,
            'resumen' => [
                'total_egresos' => $totalEgresos,
                'cantidad_egresos' => $cantidadEgresos,
                'promedio_egreso' => $promedioEgreso,
                'egresos_por_categoria' => $egresosPorCategoria
            ]
        ];
    }

    public function getReporteFacturas(string $fechaInicio, string $fechaFin): array
    {
        $facturas = Factura::with(['cliente', 'detalles'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get()
            ->map(function($factura) {
                return [
                    'numero_factura' => $factura->numero_factura,
                    'fecha' => $factura->fecha,
                    'cliente' => $factura->cliente->nombre,
                    'total' => $factura->total,
                    'cantidad_detalles' => $factura->detalles->count(),
                    'descripcion' => $factura->descripcion
                ];
            });

        $totalFacturado = $facturas->sum('total');
        $cantidadFacturas = $facturas->count();
        $promedioFactura = $cantidadFacturas > 0 ? $totalFacturado / $cantidadFacturas : 0;

        return [
            'facturas' => $facturas,
            'resumen' => [
                'total_facturado' => $totalFacturado,
                'cantidad_facturas' => $cantidadFacturas,
                'promedio_factura' => $promedioFactura
            ]
        ];
    }

    public function getReporteEmpleados(string $fechaInicio, string $fechaFin): array
    {
        $empleados = Empleado::with(['lavados' => function($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }])->get()->map(function($empleado) {
            $lavados = $empleado->lavados;
            return [
                'nombre' => $empleado->nombre,
                'telefono' => $empleado->telefono,
                'cantidad_lavados' => $lavados->count(),
                'total_ingresos' => $lavados->sum('precio'),
                'promedio_por_lavado' => $lavados->count() > 0 ? $lavados->sum('precio') / $lavados->count() : 0
            ];
        });

        return [
            'empleados' => $empleados,
            'resumen' => [
                'total_empleados' => $empleados->count(),
                'total_lavados_realizados' => $empleados->sum('cantidad_lavados'),
                'total_ingresos_generados' => $empleados->sum('total_ingresos')
            ]
        ];
    }

    public function getReporteProductos(): array
    {
        $productosAutomotrices = ProductoAutomotriz::all()->map(function($producto) {
            return [
                'nombre' => $producto->nombre,
                'stock' => $producto->stock,
                'precio_compra' => $producto->precio_compra,
                'precio_venta' => $producto->precio_venta,
                'margen' => $producto->precio_venta - $producto->precio_compra,
                'tipo' => 'Automotriz',
                'estado_stock' => $producto->stock <= 5 ? 'Bajo' : ($producto->stock <= 20 ? 'Medio' : 'Alto')
            ];
        });

        $productosDespensa = ProductoDespensa::all()->map(function($producto) {
            return [
                'nombre' => $producto->nombre,
                'stock' => $producto->stock,
                'precio_compra' => $producto->precio_compra,
                'precio_venta' => $producto->precio_venta,
                'margen' => $producto->precio_venta - $producto->precio_compra,
                'tipo' => 'Despensa',
                'estado_stock' => $producto->stock <= 5 ? 'Bajo' : ($producto->stock <= 20 ? 'Medio' : 'Alto')
            ];
        });

        $todosProductos = $productosAutomotrices->merge($productosDespensa);

        return [
            'productos' => $todosProductos,
            'resumen' => [
                'total_productos' => $todosProductos->count(),
                'productos_bajo_stock' => $todosProductos->where('estado_stock', 'Bajo')->count(),
                'valor_total_inventario' => $todosProductos->sum(function($producto) {
                    return $producto['stock'] * $producto['precio_compra'];
                }),
                'margen_total_potencial' => $todosProductos->sum(function($producto) {
                    return $producto['stock'] * $producto['margen'];
                })
            ]
        ];
    }

    public function getReporteClientes(): array
    {
        $clientes = Cliente::withCount(['lavados', 'ventasAutomotriz', 'ventasDespensa'])
            ->with(['lavados', 'ventasAutomotriz', 'ventasDespensa'])
            ->get()
            ->map(function($cliente) {
                $totalGastado = 
                    $cliente->lavados->sum('precio') + 
                    $cliente->ventasAutomotriz->sum('total') + 
                    $cliente->ventasDespensa->sum('total');
                
                $totalServicios = 
                    $cliente->lavados_count + 
                    $cliente->ventas_automotriz_count + 
                    $cliente->ventas_despensa_count;

                return [
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono,
                    'email' => $cliente->email,
                    'total_lavados' => $cliente->lavados_count,
                    'total_ventas' => $cliente->ventas_automotriz_count + $cliente->ventas_despensa_count,
                    'total_servicios' => $totalServicios,
                    'total_gastado' => $totalGastado,
                    'promedio_por_servicio' => $totalServicios > 0 ? $totalGastado / $totalServicios : 0
                ];
            });

        return [
            'clientes' => $clientes,
            'resumen' => [
                'total_clientes' => $clientes->count(),
                'clientes_activos' => $clientes->where('total_servicios', '>', 0)->count(),
                'total_ingresos_clientes' => $clientes->sum('total_gastado'),
                'promedio_gasto_por_cliente' => $clientes->count() > 0 ? $clientes->sum('total_gastado') / $clientes->count() : 0
            ]
        ];
    }

    public function getReporteFinanciero(string $fechaInicio, string $fechaFin): array
    {
        // Ingresos
        $ingresosLavados = Lavado::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('precio');
        $ingresosVentasAuto = VentaProductoAutomotriz::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('total');
        $ingresosVentasDespensa = VentaProductoDespensa::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('total');
        $otrosIngresos = Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
        $ingresosFacturas = Factura::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('total');

        $totalIngresos = $ingresosLavados + $ingresosVentasAuto + $ingresosVentasDespensa + $otrosIngresos + $ingresosFacturas;

        // Egresos
        $egresos = Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
        $gastosGenerales = GastoGeneral::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
        $pagosProveedores = PagoProveedor::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])->sum('monto');

        $totalEgresos = $egresos + $gastosGenerales + $pagosProveedores;

        // Balance
        $utilidad = $totalIngresos - $totalEgresos;
        $margenUtilidad = $totalIngresos > 0 ? ($utilidad / $totalIngresos) * 100 : 0;

        return [
            'ingresos' => [
                'lavados' => $ingresosLavados,
                'ventas_automotriz' => $ingresosVentasAuto,
                'ventas_despensa' => $ingresosVentasDespensa,
                'otros_ingresos' => $otrosIngresos,
                'facturas' => $ingresosFacturas,
                'total' => $totalIngresos
            ],
            'egresos' => [
                'egresos_operativos' => $egresos,
                'gastos_generales' => $gastosGenerales,
                'pagos_proveedores' => $pagosProveedores,
                'total' => $totalEgresos
            ],
            'balance' => [
                'utilidad' => $utilidad,
                'margen_utilidad' => $margenUtilidad,
                'estado' => $utilidad >= 0 ? 'Rentable' : 'Pérdida'
            ]
        ];
    }

    public function getReporteBalance(string $fechaInicio, string $fechaFin): array
    {
        return $this->getReporteFinanciero($fechaInicio, $fechaFin);
    }
}
