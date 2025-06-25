<?php
// Controlador para la gestión de productos automotrices
// Incluye CRUD, control de stock y activación/desactivación

namespace App\Http\Controllers;

use App\Models\ProductoAutomotriz;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductoAutomotrizController extends Controller
{
    // Listar todos los productos automotrices
    public function index()
    {
        $productos = ProductoAutomotriz::all();
        if ($productos->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay productos automotrices registrados'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Productos automotrices encontrados',
            'productos' => $productos
        ]);
    }

    // Crear un nuevo producto automotriz
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:productos_automotrices,codigo',
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric',
            'stock' => 'required|integer|min:0',
        ]);
        // Si el stock es 0, el producto se crea como inactivo
        $validated['activo'] = $validated['stock'] > 0;
        $producto = ProductoAutomotriz::create($validated);
        return response()->json([
            'message' => 'Producto automotriz creado correctamente',
            'producto' => $producto
        ], 201);
    }

    // Mostrar un producto automotriz específico
    public function show($id)
    {
        try {
            $producto = ProductoAutomotriz::findOrFail($id);
            return response()->json([
                'producto' => $producto
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto automotriz no encontrado'], 404);
        }
    }

    // Actualizar un producto automotriz
    public function update(Request $request, $id)
    {
        try {
            $producto = ProductoAutomotriz::findOrFail($id);
            $validated = $request->validate([
                'codigo' => 'sometimes|string|unique:productos_automotrices,codigo,' . $id . ',producto_automotriz_id',
                'nombre' => 'sometimes|string',
                'descripcion' => 'nullable|string',
                'precio_venta' => 'sometimes|numeric',
                'stock' => 'sometimes|integer|min:0',
                'activo' => 'sometimes|boolean',
            ]);
            // Si se actualiza el stock, actualizar el estado activo
            if (array_key_exists('stock', $validated)) {
                $producto->activo = $validated['stock'] > 0;
            }
            $producto->update($validated);
            return response()->json([
                'message' => 'Producto automotriz actualizado correctamente',
                'producto' => $producto
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto automotriz no encontrado'], 404);
        }
    }

    // Eliminar un producto automotriz
    public function destroy($id)
    {
        try {
            $producto = ProductoAutomotriz::findOrFail($id);
            $producto->delete();
            return response()->json(['message' => 'Producto automotriz eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto automotriz no encontrado'], 404);
        }
    }

    // Actualizar stock y activar producto si corresponde
    public function actualizarStock(Request $request, $id)
    {
        try {
            $producto = ProductoAutomotriz::findOrFail($id);
            $validated = $request->validate([
                'stock' => 'required|integer|min:0',
            ]);
            $producto->stock = $validated['stock'];
            // Si el stock es mayor a 0, activar el producto; si es 0, desactivar
            $producto->activo = $producto->stock > 0;
            $producto->save();
            return response()->json([
                'message' => 'Stock actualizado correctamente',
                'producto' => $producto
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto automotriz no encontrado'], 404);
        }
    }
}
