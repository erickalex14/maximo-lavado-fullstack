<?php

namespace App\Http\Controllers;

use App\Services\ProductoService;
use App\Http\Requests\ProductoAutomotriz\CreateProductoAutomotrizRequest;
use App\Http\Requests\ProductoAutomotriz\UpdateProductoAutomotrizRequest;
use App\Http\Requests\ProductoDespensa\CreateProductoDespensaRequest;
use App\Http\Requests\ProductoDespensa\UpdateProductoDespensaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    protected $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;
    }

    /**
     * Display a listing of all products (both automotriz and despensa).
     */
    public function index(): JsonResponse
    {
        try {
            $productos = $this->productoService->getAllProducts();
            
            if (empty($productos)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay productos registrados'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Productos encontrados',
                'data' => $productos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only automotriz products.
     */
    public function getProductosAutomotrices(): JsonResponse
    {
        try {
            $productos = $this->productoService->getProductosAutomotrices();
            
            return response()->json([
                'status' => 'success',
                'data' => $productos->map(function($producto) {
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
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener productos automotrices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only despensa products.
     */
    public function getProductosDespensa(): JsonResponse
    {
        try {
            $productos = $this->productoService->getProductosDespensa();
            
            return response()->json([
                'status' => 'success',
                'data' => $productos->map(function($producto) {
                    return [
                        'id' => $producto->producto_despensa_id,
                        'nombre' => $producto->nombre,
                        'precio_venta' => $producto->precio_venta,
                        'stock' => $producto->stock,
                        'descripcion' => $producto->descripcion,
                        'activo' => $producto->activo,
                        'tipo' => 'despensa'
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener productos de despensa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product metrics.
     */
    public function getMetricas(): JsonResponse
    {
        try {
            $metricas = $this->productoService->getMetricas();
            
            return response()->json([
                'status' => 'success',
                'data' => $metricas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener métricas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new automotriz product.
     */
    public function storeAutomotriz(CreateProductoAutomotrizRequest $request): JsonResponse
    {
        try {
            $producto = $this->productoService->createProductoAutomotriz($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Producto automotriz creado correctamente',
                'data' => $producto
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear producto automotriz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new despensa product.
     */
    public function storeDespensa(CreateProductoDespensaRequest $request): JsonResponse
    {
        try {
            $producto = $this->productoService->createProductoDespensa($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Producto de despensa creado correctamente',
                'data' => $producto
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear producto de despensa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified automotriz product.
     */
    public function showAutomotriz(int $id): JsonResponse
    {
        try {
            $producto = $this->productoService->findProductoAutomotrizById($id);
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto automotriz no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $producto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener producto automotriz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified despensa product.
     */
    public function showDespensa(int $id): JsonResponse
    {
        try {
            $producto = $this->productoService->findProductoDespensaById($id);
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto de despensa no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $producto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener producto de despensa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified automotriz product.
     */
    public function updateAutomotriz(UpdateProductoAutomotrizRequest $request, int $id): JsonResponse
    {
        try {
            $producto = $this->productoService->updateProductoAutomotriz($id, $request->validated());
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto automotriz no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Producto automotriz actualizado correctamente',
                'data' => $producto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar producto automotriz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified despensa product.
     */
    public function updateDespensa(UpdateProductoDespensaRequest $request, int $id): JsonResponse
    {
        try {
            $producto = $this->productoService->updateProductoDespensa($id, $request->validated());
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto de despensa no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Producto de despensa actualizado correctamente',
                'data' => $producto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar producto de despensa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified automotriz product.
     */
    public function destroyAutomotriz(int $id): JsonResponse
    {
        try {
            $deleted = $this->productoService->deleteProductoAutomotriz($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto automotriz no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Producto automotriz eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar producto automotriz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified despensa product.
     */
    public function destroyDespensa(int $id): JsonResponse
    {
        try {
            $deleted = $this->productoService->deleteProductoDespensa($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto de despensa no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Producto de despensa eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar producto de despensa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stock for automotriz product.
     */
    public function updateStockAutomotriz(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'stock' => 'required|integer|min:0',
            ]);

            $producto = $this->productoService->updateStockAutomotriz($id, $validated['stock']);
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto automotriz no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stock actualizado correctamente',
                'data' => $producto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stock for despensa product.
     */
    public function updateStockDespensa(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'stock' => 'required|integer|min:0',
            ]);

            $producto = $this->productoService->updateStockDespensa($id, $validated['stock']);
            
            if (!$producto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Producto de despensa no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stock actualizado correctamente',
                'data' => $producto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar stock: ' . $e->getMessage()
            ], 500);
        }
    }
}
