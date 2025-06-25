<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingreso;
use App\Models\Egreso;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Lavado;
use PDF; // Requiere barryvdh/laravel-dompdf

class ReporteController extends Controller
{
    // Mostrar la vista de reportes
    public function indexView()
    {
        return view('reportes.index');
    }

    // Obtener datos del resumen financiero
    public function getResumenFinanciero(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());

        $ingresosTotales = Ingreso::whereBetween('fecha', [$fechaDesde, $fechaHasta])->sum('monto');
        $egresosTotales = Egreso::whereBetween('fecha', [$fechaDesde, $fechaHasta])->sum('monto');
        $gananciaNeta = $ingresosTotales - $egresosTotales;
        $margenPorcentaje = $ingresosTotales > 0 ? round(($gananciaNeta / $ingresosTotales) * 100, 2) : 0;

        return response()->json([
            'ingresosTotales' => $ingresosTotales,
            'egresosTotales' => $egresosTotales,
            'gananciaNeta' => $gananciaNeta,
            'margenPorcentaje' => $margenPorcentaje
        ]);
    }    // Reporte de ingresos en PDF
    public function ingresosPDF(Request $request)
    {
        $ingresos = Ingreso::orderByDesc('fecha')->get();
        $pdf = PDF::loadView('reportes.ingresos', compact('ingresos'));
        return $pdf->download('reporte_ingresos.pdf');
    }

    // Reporte de egresos en PDF
    public function egresosPDF(Request $request)
    {
        $egresos = Egreso::orderByDesc('fecha')->get();
        $pdf = PDF::loadView('reportes.egresos', compact('egresos'));
        return $pdf->download('reporte_egresos.pdf');
    }

    // Reporte de inventario en PDF
    public function inventarioPDF(Request $request)
    {
        $productosAutomotrices = ProductoAutomotriz::all();
        $productosDespensa = ProductoDespensa::all();
        $pdf = PDF::loadView('reportes.inventario', compact('productosAutomotrices', 'productosDespensa'));
        return $pdf->download('reporte_inventario.pdf');
    }

    // Reporte de pagos a proveedores en PDF
    public function pagosPDF(Request $request)
    {
        $pagos = PagoProveedor::with('proveedor')->orderByDesc('fecha_pago')->get();
        $pdf = PDF::loadView('reportes.pagos', compact('pagos'));
        return $pdf->download('reporte_pagos.pdf');
    }

    // Reporte de deudas a proveedores en PDF
    public function deudasPDF(Request $request)
    {
        $proveedores = Proveedor::where('deuda_pendiente', '>', 0)->get();
        $pdf = PDF::loadView('reportes.deudas', compact('proveedores'));
        return $pdf->download('reporte_deudas.pdf');
    }

    // PDF de factura individual
    public function facturaPDF($id)
    {
        $factura = Factura::with(['cliente', 'detalles'])->findOrFail($id);
        $pdf = PDF::loadView('reportes.factura', compact('factura'));
        return $pdf->download('factura_'.$factura->numero_factura.'.pdf');
    }

    // API para obtener datos de ventas
    public function getVentasData(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());

        // Ventas por día de la semana actual
        $ventasPorDia = collect();
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $monto = VentaProductoAutomotriz::whereDate('created_at', $fecha)->sum('total') +
                     VentaProductoDespensa::whereDate('created_at', $fecha)->sum('total');
            
            $ventasPorDia->push([
                'fecha' => $fecha->toDateString(),
                'dia' => $fecha->format('D'),
                'monto' => (float) $monto
            ]);
        }

        // Top productos más vendidos
        $topProductosAuto = VentaProductoAutomotriz::with('producto')
            ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->selectRaw('producto_automotriz_id, SUM(cantidad) as total_cantidad, SUM(total) as total_monto')
            ->groupBy('producto_automotriz_id')
            ->orderByDesc('total_monto')
            ->limit(5)
            ->get();

        $topProductosDespensa = VentaProductoDespensa::with('producto')
            ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->selectRaw('producto_despensa_id, SUM(cantidad) as total_cantidad, SUM(total) as total_monto')
            ->groupBy('producto_despensa_id')
            ->orderByDesc('total_monto')
            ->limit(5)
            ->get();

        $topProductos = $topProductosAuto->map(function($venta) {
            return [
                'nombre' => $venta->producto->nombre ?? 'Producto no encontrado',
                'cantidad' => $venta->total_cantidad,
                'monto' => (float) $venta->total_monto
            ];
        })->concat($topProductosDespensa->map(function($venta) {
            return [
                'nombre' => $venta->producto->nombre ?? 'Producto no encontrado',
                'cantidad' => $venta->total_cantidad,
                'monto' => (float) $venta->total_monto
            ];
        }))->sortByDesc('monto')->take(5)->values();

        // Detalle de ventas
        $detalleVentas = collect();
        
        $ventasAuto = VentaProductoAutomotriz::with('producto')
            ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->latest()
            ->limit(10)
            ->get();
            
        $ventasDespensa = VentaProductoDespensa::with('producto')
            ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->latest()
            ->limit(10)
            ->get();

        foreach ($ventasAuto as $venta) {
            $detalleVentas->push([
                'id' => $venta->id,
                'fecha' => $venta->created_at->format('d/m/Y'),
                'producto' => $venta->producto->nombre ?? 'Producto no encontrado',
                'cantidad' => $venta->cantidad,
                'precio' => (float) $venta->precio_unitario,
                'total' => (float) $venta->total
            ]);
        }

        foreach ($ventasDespensa as $venta) {
            $detalleVentas->push([
                'id' => 'D' . $venta->id,
                'fecha' => $venta->created_at->format('d/m/Y'),
                'producto' => $venta->producto->nombre ?? 'Producto no encontrado',
                'cantidad' => $venta->cantidad,
                'precio' => (float) $venta->precio_unitario,
                'total' => (float) $venta->total
            ]);
        }

        return response()->json([
            'ventasPorDia' => $ventasPorDia,
            'topProductos' => $topProductos,
            'detalleVentas' => $detalleVentas->sortByDesc('fecha')->values()
        ]);
    }

    // API para obtener datos de servicios
    public function getServiciosData(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());

        // Servicios más populares (lavados)
        $serviciosPopulares = Lavado::whereBetween('fecha', [$fechaDesde, $fechaHasta])
            ->selectRaw('tipo_servicio, COUNT(*) as cantidad, SUM(precio) as ingresos')
            ->groupBy('tipo_servicio')
            ->orderByDesc('ingresos')
            ->get()
            ->map(function($servicio) {
                return [
                    'tipo' => $servicio->tipo_servicio ?? 'Lavado estándar',
                    'cantidad' => $servicio->cantidad,
                    'ingresos' => (float) $servicio->ingresos
                ];
            });

        return response()->json([
            'serviciosPopulares' => $serviciosPopulares
        ]);
    }

    // API para obtener datos de clientes
    public function getClientesData(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());

        // Top clientes por facturación
        $topClientes = Cliente::withSum(['facturas as total_gastado' => function($query) use ($fechaDesde, $fechaHasta) {
            $query->whereBetween('fecha_emision', [$fechaDesde, $fechaHasta]);
        }], 'monto_total')
        ->withCount(['facturas as visitas' => function($query) use ($fechaDesde, $fechaHasta) {
            $query->whereBetween('fecha_emision', [$fechaDesde, $fechaHasta]);
        }])
        ->having('total_gastado', '>', 0)
        ->orderByDesc('total_gastado')
        ->limit(5)
        ->get()
        ->map(function($cliente) {
            return [
                'nombre' => $cliente->nombre,
                'visitas' => $cliente->visitas,
                'gastado' => (float) $cliente->total_gastado
            ];
        });

        // Análisis de clientes
        $clientesTotales = Cliente::count();
        $clientesNuevos = Cliente::whereBetween('created_at', [$fechaDesde, $fechaHasta])->count();
        $clientesRecurrentes = Cliente::whereHas('facturas', function($query) use ($fechaDesde, $fechaHasta) {
            $query->whereBetween('fecha_emision', [$fechaDesde, $fechaHasta]);
        })->count();
        
        $promedioVisitas = $clientesTotales > 0 ? 
            Factura::whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])->count() / $clientesTotales : 0;

        $analisisClientes = [
            'nuevos' => $clientesNuevos,
            'recurrentes' => $clientesRecurrentes,
            'promedioVisitas' => round($promedioVisitas, 1)
        ];

        return response()->json([
            'topClientes' => $topClientes,
            'analisisClientes' => $analisisClientes
        ]);
    }

    // API para obtener datos de empleados
    public function getEmpleadosData(Request $request)
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());

        // Productividad por empleado basada en lavados
        $productividadEmpleados = Empleado::withCount(['lavados as servicios' => function($query) use ($fechaDesde, $fechaHasta) {
            $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
        }])
        ->withSum(['lavados as ingresos' => function($query) use ($fechaDesde, $fechaHasta) {
            $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
        }], 'precio')
        ->having('servicios', '>', 0)
        ->orderByDesc('ingresos')
        ->get()
        ->map(function($empleado) {
            return [
                'nombre' => $empleado->nombre,
                'servicios' => $empleado->servicios,
                'ingresos' => (float) $empleado->ingresos
            ];
        });

        return response()->json([
            'productividadEmpleados' => $productividadEmpleados
        ]);
    }
}
