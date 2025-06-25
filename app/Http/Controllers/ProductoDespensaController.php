<?php
// Controlador para la gestión de productos de despensa
// Incluye CRUD, control de stock y activación/desactivación automática

namespace App\Http\Controllers;

use App\Models\ProductoDespensa;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductoDespensaController extends Controller
{
    // Listar todos los productos de despensa
    public function index()
    {
        $productos = ProductoDespensa::all();
        if ($productos->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay productos de despensa registrados'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Productos de despensa encontrados',
            'productos' => $productos
        ]);
    }

    // Crear un nuevo producto de despensa
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio_venta' => 'required|numeric',
            'stock' => 'required|integer|min:0',
        ]);
        // Si el stock es 0, el producto se crea como inactivo
        $validated['activo'] = $validated['stock'] > 0;
        $producto = ProductoDespensa::create($validated);
        return response()->json([
            'message' => 'Producto de despensa creado correctamente',
            'producto' => $producto
        ], 201);
    }

    // Mostrar un producto de despensa específico
    public function show($id)
    {
        try {
            $producto = ProductoDespensa::findOrFail($id);
            return response()->json([
                'producto' => $producto
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto de despensa no encontrado'], 404);
        }
    }

    // Actualizar un producto de despensa
    public function update(Request $request, $id)
    {
        try {
            $producto = ProductoDespensa::findOrFail($id);
            $validated = $request->validate([
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
                'message' => 'Producto de despensa actualizado correctamente',
                'producto' => $producto
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto de despensa no encontrado'], 404);
        }
    }

    // Eliminar un producto de despensa
    public function destroy($id)
    {
        try {
            $producto = ProductoDespensa::findOrFail($id);
            $producto->delete();
            return response()->json(['message' => 'Producto de despensa eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto de despensa no encontrado'], 404);
        }
    }

    // Actualizar stock y activar producto si corresponde
    public function actualizarStock(Request $request, $id)
    {
        try {
            $producto = ProductoDespensa::findOrFail($id);
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
            return response()->json(['message' => 'Producto de despensa no encontrado'], 404);
        }
    }
}
