<?php
namespace App\Http\Controllers;

use App\Models\VentaProductoAutomotriz;
use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaProductoAutomotrizController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos_automotrices,producto_automotriz_id',
            'cliente_id' => 'nullable|exists:clientes,cliente_id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
        ]);
        return DB::transaction(function () use ($validated) {
            $total = $validated['cantidad'] * $validated['precio_unitario'];
            $venta = VentaProductoAutomotriz::create([
                'producto_id' => $validated['producto_id'],
                'cliente_id' => $validated['cliente_id'] ?? null,
                'cantidad' => $validated['cantidad'],
                'precio_unitario' => $validated['precio_unitario'],
                'total' => $total,
                'fecha' => $validated['fecha'],
            ]);
            $ingreso = Ingreso::create([
                'fecha' => $validated['fecha'],
                'tipo' => 'producto_automotriz',
                'referencia_id' => $venta->id,
                'monto' => $total,
                'descripcion' => 'Venta producto automotriz',
            ]);
            return response()->json([
                'message' => 'Venta y ingreso registrados',
                'venta' => $venta,
                'ingreso' => $ingreso
            ], 201);
        });
    }
}
