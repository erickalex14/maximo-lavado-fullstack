<?php

namespace App\Http\Controllers;

use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use App\Models\VentaProductoAutomotriz;
use App\Models\VentaProductoDespensa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    // Mostrar la vista de productos
    public function indexView()
    {
        return view('productos.index');
    }    // Obtener todos los productos automotrices
    public function getProductosAutomotrices()
    {
        $productos = ProductoAutomotriz::all()->map(function($producto) {
            return [
                'id' => $producto->producto_automotriz_id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'precio_venta' => $producto->precio_venta,
                'stock' => $producto->stock,
                'descripcion' => $producto->descripcion,
                'activo' => $producto->activo,
                'tipo' => 'automotriz'
            ];
        });

        return response()->json([
            'status' => 'success',
            'productos' => $productos
        ]);
    }    // Obtener todos los productos de despensa
    public function getProductosDespensa()
    {
        $productos = ProductoDespensa::all()->map(function($producto) {
            return [
                'id' => $producto->producto_despensa_id,
                'nombre' => $producto->nombre,
                'precio_venta' => $producto->precio_venta,
                'stock' => $producto->stock,
                'descripcion' => $producto->descripcion,
                'activo' => $producto->activo,
                'tipo' => 'despensa'
            ];
        });

        return response()->json([
            'status' => 'success',
            'productos' => $productos
        ]);
    }

    // Obtener métricas de productos
    public function getMetricas()
    {
        $totalAutomotrices = ProductoAutomotriz::count();
        $totalDespensa = ProductoDespensa::count();
        $stockBajoAutomotrices = ProductoAutomotriz::where('stock', '<=', 10)->count();
        $stockBajoDespensa = ProductoDespensa::where('stock', '<=', 10)->count();        $valorInventarioAutomotrices = ProductoAutomotriz::sum(DB::raw('precio_venta * stock'));
        $valorInventarioDespensa = ProductoDespensa::sum(DB::raw('precio_venta * stock'));

        return response()->json([
            'totalAutomotrices' => $totalAutomotrices,
            'totalDespensa' => $totalDespensa,
            'stockBajo' => $stockBajoAutomotrices + $stockBajoDespensa,
            'valorInventario' => $valorInventarioAutomotrices + $valorInventarioDespensa
        ]);
    }    // Crear producto automotriz
    public function storeAutomotriz(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:productos_automotrices,codigo',
            'nombre' => 'required|string',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $producto = ProductoAutomotriz::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Producto automotriz creado exitosamente',
            'producto' => $producto
        ], 201);
    }    // Crear producto de despensa
    public function storeDespensa(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $producto = ProductoDespensa::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Producto de despensa creado exitosamente',
            'producto' => $producto
        ], 201);
    }    // Actualizar producto automotriz
    public function updateAutomotriz(Request $request, $id)
    {
        $producto = ProductoAutomotriz::findOrFail($id);
        
        $validated = $request->validate([
            'codigo' => 'sometimes|string|unique:productos_automotrices,codigo,' . $id . ',producto_automotriz_id',
            'nombre' => 'sometimes|string',
            'precio_venta' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'descripcion' => 'sometimes|string',
            'activo' => 'sometimes|boolean'
        ]);

        $producto->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Producto automotriz actualizado exitosamente',
            'producto' => $producto
        ]);
    }    // Actualizar producto de despensa
    public function updateDespensa(Request $request, $id)
    {
        $producto = ProductoDespensa::findOrFail($id);
        
        $validated = $request->validate([
            'nombre' => 'sometimes|string',
            'precio_venta' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'descripcion' => 'sometimes|string',
            'activo' => 'sometimes|boolean'
        ]);

        $producto->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Producto de despensa actualizado exitosamente',
            'producto' => $producto
        ]);
    }

    // Eliminar producto automotriz
    public function destroyAutomotriz($id)
    {
        $producto = ProductoAutomotriz::findOrFail($id);
        $producto->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Producto automotriz eliminado exitosamente'
        ]);
    }

    // Eliminar producto de despensa
    public function destroyDespensa($id)
    {
        $producto = ProductoDespensa::findOrFail($id);
        $producto->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Producto de despensa eliminado exitosamente'
        ]);
    }
}
