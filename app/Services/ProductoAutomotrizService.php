<?php

namespace App\Services;

use App\Contracts\ProductoAutomotrizRepositoryInterface;
use App\Models\ProductoAutomotriz;
use Illuminate\Database\Eloquent\Collection;

class ProductoAutomotrizService
{
    protected $productoAutomotrizRepository;

    public function __construct(ProductoAutomotrizRepositoryInterface $productoAutomotrizRepository)
    {
        $this->productoAutomotrizRepository = $productoAutomotrizRepository;
    }

    public function getAllProductos(): Collection
    {
        return $this->productoAutomotrizRepository->getAll();
    }

    public function getActiveProductos(): Collection
    {
        return $this->productoAutomotrizRepository->getActiveProducts();
    }

    public function getInactiveProductos(): Collection
    {
        return $this->productoAutomotrizRepository->getInactiveProducts();
    }

    public function findProductoById(int $id): ?ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->findById($id);
    }

    public function findProductoByCodigo(string $codigo): ?ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->findByCodigo($codigo);
    }

    public function createProducto(array $data): ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->create($data);
    }

    public function updateProducto(int $id, array $data): ?ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->update($id, $data);
    }

    public function deleteProducto(int $id): bool
    {
        return $this->productoAutomotrizRepository->delete($id);
    }

    public function updateStock(int $id, int $newStock): ?ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->updateStock($id, $newStock);
    }

    public function toggleActive(int $id): ?ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->toggleActive($id);
    }

    public function reduceStock(int $id, int $quantity): ?ProductoAutomotriz
    {
        $producto = $this->findProductoById($id);
        if ($producto && $producto->stock >= $quantity) {
            $newStock = $producto->stock - $quantity;
            return $this->updateStock($id, $newStock);
        }
        return null;
    }

    public function increaseStock(int $id, int $quantity): ?ProductoAutomotriz
    {
        $producto = $this->findProductoById($id);
        if ($producto) {
            $newStock = $producto->stock + $quantity;
            return $this->updateStock($id, $newStock);
        }
        return null;
    }
}
