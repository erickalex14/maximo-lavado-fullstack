<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    // Mostrar la vista de facturas
    public function indexView()
    {
        return view('facturas.index');
    }

    // Listar facturas
    public function index(Request $request)
    {
        $facturas = Factura::with('cliente')->orderByDesc('fecha')->paginate(20);
        return response()->json($facturas);
    }

    // Crear factura
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,cliente_id',
            'fecha' => 'required|date',
            'descripcion' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.venta_producto_automotriz_id' => 'nullable|exists:ventas_productos_automotrices,id',
            'detalles.*.venta_producto_despensa_id' => 'nullable|exists:ventas_productos_despensa,id',
            'detalles.*.lavado_id' => 'nullable|integer',
            'detalles.*.cantidad' => 'required|numeric|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $factura = Factura::create([
                'cliente_id' => $validated['cliente_id'],
                'fecha' => $validated['fecha'],
                'descripcion' => $validated['descripcion'] ?? null,
                'total' => 0, // Se calcula después
                'numero_factura' => 'F-' . time(),
            ]);

            $total = 0;
            foreach ($validated['detalles'] as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                FacturaDetalle::create([
                    'factura_id' => $factura->factura_id,
                    'venta_producto_automotriz_id' => $detalle['venta_producto_automotriz_id'] ?? null,
                    'venta_producto_despensa_id' => $detalle['venta_producto_despensa_id'] ?? null,
                    'lavado_id' => $detalle['lavado_id'] ?? null,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
                $total += $subtotal;
            }
            $factura->total = $total;
            $factura->save();
            return response()->json($factura->load('detalles'), 201);
        });
    }

    // Mostrar factura
    public function show($id)
    {
        $factura = Factura::with(['cliente', 'detalles'])->findOrFail($id);
        return response()->json($factura);
    }

    // Actualizar factura
    public function update(Request $request, $id)
    {
        $factura = Factura::findOrFail($id);
        $validated = $request->validate([
            'fecha' => 'sometimes|date',
            'descripcion' => 'nullable|string',
            'detalles' => 'sometimes|array|min:1',
            'detalles.*.factura_detalle_id' => 'sometimes|integer|exists:factura_detalles,factura_detalle_id',
            'detalles.*.venta_producto_automotriz_id' => 'nullable|exists:ventas_productos_automotrices,id',
            'detalles.*.venta_producto_despensa_id' => 'nullable|exists:ventas_productos_despensa,id',
            'detalles.*.lavado_id' => 'nullable|integer',
            'detalles.*.cantidad' => 'required_with:detalles.*.precio_unitario|numeric|min:1',
            'detalles.*.precio_unitario' => 'required_with:detalles.*.cantidad|numeric|min:0',
        ]);

        if (isset($validated['fecha'])) $factura->fecha = $validated['fecha'];
        if (isset($validated['descripcion'])) $factura->descripcion = $validated['descripcion'];
        $factura->save();

        $total = 0;
        if (isset($validated['detalles'])) {
            foreach ($validated['detalles'] as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                if (isset($detalle['factura_detalle_id'])) {
                    $facturaDetalle = FacturaDetalle::findOrFail($detalle['factura_detalle_id']);
                    $facturaDetalle->update([
                        'venta_producto_automotriz_id' => $detalle['venta_producto_automotriz_id'] ?? null,
                        'venta_producto_despensa_id' => $detalle['venta_producto_despensa_id'] ?? null,
                        'lavado_id' => $detalle['lavado_id'] ?? null,
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $subtotal,
                    ]);
                } else {
                    FacturaDetalle::create([
                        'factura_id' => $factura->factura_id,
                        'venta_producto_automotriz_id' => $detalle['venta_producto_automotriz_id'] ?? null,
                        'venta_producto_despensa_id' => $detalle['venta_producto_despensa_id'] ?? null,
                        'lavado_id' => $detalle['lavado_id'] ?? null,
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $subtotal,
                    ]);
                }
                $total += $subtotal;
            }
            $factura->total = $total;
            $factura->save();
        }
        return response()->json($factura->load('detalles'));
    }

    // Eliminar factura
    public function destroy($id)
    {
        $factura = Factura::findOrFail($id);
        $factura->detalles()->delete();
        $factura->delete();
        return response()->json(['message' => 'Factura eliminada']);
    }

    // Obtener detalles de factura
    public function detalles($id)
    {
        $factura = Factura::with('detalles')->findOrFail($id);
        return response()->json($factura->detalles);
    }

    // Generar PDF de factura
    public function generarPDF($id)
    {
        $factura = \App\Models\Factura::with(['cliente', 'detalles'])->findOrFail($id);
        $pdf = \PDF::loadView('reportes.factura', compact('factura'));
        return $pdf->download('factura_' . $factura->numero_factura . '.pdf');
    }
}
