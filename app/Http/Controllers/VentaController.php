<?php

namespace App\Http\Controllers;

use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VentaController extends Controller
{
    // Mostrar la vista de ventas
    public function indexView()
    {
        return view('ventas.index');
    }

    // Obtener métricas de ventas
    public function getMetricas()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $ventasHoy = VentaProductoAutomotriz::whereDate('created_at', $today)->count() + 
                    VentaProductoDespensa::whereDate('created_at', $today)->count();

        $ventasMes = VentaProductoAutomotriz::where('created_at', '>=', $thisMonth)->count() + 
                    VentaProductoDespensa::where('created_at', '>=', $thisMonth)->count();

        $ingresoHoy = VentaProductoAutomotriz::whereDate('created_at', $today)->sum('total') + 
                     VentaProductoDespensa::whereDate('created_at', $today)->sum('total');

        $ingresoMes = VentaProductoAutomotriz::where('created_at', '>=', $thisMonth)->sum('total') + 
                     VentaProductoDespensa::where('created_at', '>=', $thisMonth)->sum('total');

        return response()->json([
            'ventasHoy' => $ventasHoy,
            'ventasMes' => $ventasMes,
            'ingresoHoy' => $ingresoHoy,
            'ingresoMes' => $ingresoMes
        ]);
    }

    // Obtener todas las ventas
    public function getVentas()
    {        $ventasAutomotrices = VentaProductoAutomotriz::with(['productoAutomotriz', 'cliente'])
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                    'producto' => $venta->productoAutomotriz->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'automotriz'
                ];
            });

        $ventasDespensa = VentaProductoDespensa::with(['productoDespensa', 'cliente'])
            ->get()
            ->map(function($venta) {
                return [
                    'id' => $venta->id,
                    'fecha' => $venta->fecha,
                    'cliente' => $venta->cliente ? $venta->cliente->nombre : 'Cliente General',
                    'producto' => $venta->productoDespensa->nombre,
                    'cantidad' => $venta->cantidad,
                    'precio_unitario' => $venta->precio_unitario,
                    'total' => $venta->total,
                    'tipo' => 'despensa'
                ];
            });

        $todasLasVentas = $ventasAutomotrices->merge($ventasDespensa)
            ->sortByDesc('fecha')
            ->values();

        return response()->json([
            'status' => 'success',
            'ventas' => $todasLasVentas
        ]);
    }

    // Obtener productos disponibles para venta
    public function getProductosDisponibles()
    {        $automotrices = ProductoAutomotriz::where('stock', '>', 0)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->producto_automotriz_id,
                    'nombre' => $producto->nombre,
                    'precio_venta' => $producto->precio_venta,
                    'stock' => $producto->stock,
                    'tipo' => 'automotriz'
                ];
            });

        $despensa = ProductoDespensa::where('stock', '>', 0)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->producto_despensa_id,
                    'nombre' => $producto->nombre,
                    'precio_venta' => $producto->precio_venta,
                    'stock' => $producto->stock,
                    'tipo' => 'despensa'
                ];
            });

        return response()->json([
            'automotrices' => $automotrices,
            'despensa' => $despensa
        ]);
    }    // Registrar venta
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer',
            'items.*.tipo' => 'required|in:automotriz,despensa',
            'items.*.cantidad' => 'required|integer|min:1',
            'cliente_id' => 'nullable|exists:clientes,cliente_id'
        ]);

        $ventasCreadas = [];
        $montoTotal = 0;

        foreach ($validated['items'] as $item) {
            if ($item['tipo'] === 'automotriz') {
                $producto = ProductoAutomotriz::findOrFail($item['producto_id']);
                
                if ($producto->stock < $item['cantidad']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}"
                    ], 400);
                }

                $precioUnitario = $producto->precio_venta;
                $total = $precioUnitario * $item['cantidad'];

                $venta = VentaProductoAutomotriz::create([
                    'producto_id' => $item['producto_id'],
                    'cliente_id' => $validated['cliente_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'total' => $total,
                    'fecha' => now()
                ]);

                // Actualizar stock
                $producto->decrement('stock', $item['cantidad']);
                
                $ventasCreadas[] = $venta;
                $montoTotal += $total;

            } else {
                $producto = ProductoDespensa::findOrFail($item['producto_id']);
                
                if ($producto->stock < $item['cantidad']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}"
                    ], 400);
                }

                $precioUnitario = $producto->precio_venta;
                $total = $precioUnitario * $item['cantidad'];

                $venta = VentaProductoDespensa::create([
                    'producto_id' => $item['producto_id'],
                    'cliente_id' => $validated['cliente_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'total' => $total,
                    'fecha' => now()
                ]);

                // Actualizar stock
                $producto->decrement('stock', $item['cantidad']);
                
                $ventasCreadas[] = $venta;
                $montoTotal += $total;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Venta registrada exitosamente',
            'ventas' => $ventasCreadas,
            'monto_total' => $montoTotal
        ], 201);
    }

    // Obtener clientes para ventas
    public function getClientes()
    {
        $clientes = Cliente::select('cliente_id as id', 'nombre', 'telefono')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'status' => 'success',
            'clientes' => $clientes
        ]);
    }
}
