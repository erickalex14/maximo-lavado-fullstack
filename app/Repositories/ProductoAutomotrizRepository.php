<?php

namespace App\Repositories;

use App\Contracts\ProductoAutomotrizRepositoryInterface;
use App\Models\ProductoAutomotriz;
use Illuminate\Database\Eloquent\Collection;

class ProductoAutomotrizRepository implements ProductoAutomotrizRepositoryInterface
{
    protected $model;

    public function __construct(ProductoAutomotriz $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?ProductoAutomotriz
    {
        return $this->model->where('producto_automotriz_id', $id)->first();
    }

    public function create(array $data): ProductoAutomotriz
    {
        // Si el stock es 0, el producto se crea como inactivo
        if (isset($data['stock']) && $data['stock'] == 0) {
            $data['activo'] = false;
        } else {
            $data['activo'] = $data['activo'] ?? true;
        }
        
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?ProductoAutomotriz
    {
        $producto = $this->findById($id);
        if ($producto) {
            // Si se actualiza el stock a 0, desactivar el producto
            if (isset($data['stock']) && $data['stock'] == 0) {
                $data['activo'] = false;
            }
            
            $producto->update($data);
            return $producto->fresh();
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $producto = $this->findById($id);
        if ($producto) {
            return $producto->delete();
        }
        return false;
    }

    /**
     * Restaurar producto automotriz eliminado lógicamente
     */
    public function restore(int $id): bool
    {
        $producto = ProductoAutomotriz::onlyTrashed()->find($id);
        if ($producto) {
            return $producto->restore();
        }
        return false;
    }

    /**
     * Obtener productos automotrices eliminados lógicamente
     */
    public function getTrashed(): Collection
    {
        return ProductoAutomotriz::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    public function getActiveProducts(): Collection
    {
        return $this->model->where('activo', true)->get();
    }

    public function getInactiveProducts(): Collection
    {
        return $this->model->where('activo', false)->get();
    }

    public function findByCodigo(string $codigo): ?ProductoAutomotriz
    {
        return $this->model->where('codigo', $codigo)->first();
    }

    public function updateStock(int $id, int $newStock): ?ProductoAutomotriz
    {
        $producto = $this->findById($id);
        if ($producto) {
            $data = ['stock' => $newStock];
            // Si el stock llega a 0, desactivar el producto
            if ($newStock == 0) {
                $data['activo'] = false;
            }
            
            $producto->update($data);
            return $producto->fresh();
        }
        return null;
    }

    public function toggleActive(int $id): ?ProductoAutomotriz
    {
        $producto = $this->findById($id);
        if ($producto) {
            $producto->update(['activo' => !$producto->activo]);
            return $producto->fresh();
        }
        return null;
    }

    /**
     * Get metrics for automotriz products
     */
    public function getMetricas(): array
    {
        $total = $this->model->count();
        $activos = $this->model->where('activo', true)->count();
        $inactivos = $total - $activos;
        $stockBajo = $this->model->where('stock', '<=', 5)->count();
        $sinStock = $this->model->where('stock', 0)->count();
        $valorInventario = $this->model->sum(\DB::raw('precio_venta * stock'));
        
        return [
            'productos_automotrices' => [
                'total' => $total,
                'activos' => $activos,
                'inactivos' => $inactivos,
                'stock_bajo' => $stockBajo,
                'sin_stock' => $sinStock,
                'valor_inventario' => round($valorInventario, 2)
            ]
        ];
    }
}
