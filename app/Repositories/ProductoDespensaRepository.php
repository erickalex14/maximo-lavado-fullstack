<?php

namespace App\Repositories;

use App\Contracts\ProductoDespensaRepositoryInterface;
use App\Models\ProductoDespensa;
use Illuminate\Database\Eloquent\Collection;

class ProductoDespensaRepository implements ProductoDespensaRepositoryInterface
{
    protected $model;

    public function __construct(ProductoDespensa $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?ProductoDespensa
    {
        return $this->model->where('producto_despensa_id', $id)->first();
    }

    public function create(array $data): ProductoDespensa
    {
        // Si el stock es 0, el producto se crea como inactivo
        if (isset($data['stock']) && $data['stock'] == 0) {
            $data['activo'] = false;
        } else {
            $data['activo'] = $data['activo'] ?? true;
        }
        
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?ProductoDespensa
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

    public function getActiveProducts(): Collection
    {
        return $this->model->where('activo', true)->get();
    }

    public function getInactiveProducts(): Collection
    {
        return $this->model->where('activo', false)->get();
    }

    public function updateStock(int $id, int $newStock): ?ProductoDespensa
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

    public function toggleActive(int $id): ?ProductoDespensa
    {
        $producto = $this->findById($id);
        if ($producto) {
            $producto->update(['activo' => !$producto->activo]);
            return $producto->fresh();
        }
        return null;
    }
}
