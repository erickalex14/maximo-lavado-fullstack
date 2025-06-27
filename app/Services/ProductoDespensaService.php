<?php

namespace App\Services;

use App\Contracts\ProductoDespensaRepositoryInterface;
use App\Models\ProductoDespensa;
use Illuminate\Database\Eloquent\Collection;

class ProductoDespensaService
{
    protected $productoDespensaRepository;

    public function __construct(ProductoDespensaRepositoryInterface $productoDespensaRepository)
    {
        $this->productoDespensaRepository = $productoDespensaRepository;
    }

    public function getAllProductos(): Collection
    {
        return $this->productoDespensaRepository->getAll();
    }

    public function getActiveProductos(): Collection
    {
        return $this->productoDespensaRepository->getActiveProducts();
    }

    public function getInactiveProductos(): Collection
    {
        return $this->productoDespensaRepository->getInactiveProducts();
    }

    public function findProductoById(int $id): ?ProductoDespensa
    {
        return $this->productoDespensaRepository->findById($id);
    }

    public function createProducto(array $data): ProductoDespensa
    {
        return $this->productoDespensaRepository->create($data);
    }

    public function updateProducto(int $id, array $data): ?ProductoDespensa
    {
        return $this->productoDespensaRepository->update($id, $data);
    }

    public function deleteProducto(int $id): bool
    {
        return $this->productoDespensaRepository->delete($id);
    }

    public function updateStock(int $id, int $newStock): ?ProductoDespensa
    {
        return $this->productoDespensaRepository->updateStock($id, $newStock);
    }

    public function toggleActive(int $id): ?ProductoDespensa
    {
        return $this->productoDespensaRepository->toggleActive($id);
    }

    public function reduceStock(int $id, int $quantity): ?ProductoDespensa
    {
        $producto = $this->findProductoById($id);
        if ($producto && $producto->stock >= $quantity) {
            $newStock = $producto->stock - $quantity;
            return $this->updateStock($id, $newStock);
        }
        return null;
    }

    public function increaseStock(int $id, int $quantity): ?ProductoDespensa
    {
        $producto = $this->findProductoById($id);
        if ($producto) {
            $newStock = $producto->stock + $quantity;
            return $this->updateStock($id, $newStock);
        }
        return null;
    }
}
