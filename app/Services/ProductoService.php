<?php

namespace App\Services;

use App\Contracts\ProductoAutomotrizRepositoryInterface;
use App\Contracts\ProductoDespensaRepositoryInterface;
use App\Models\ProductoAutomotriz;
use App\Models\ProductoDespensa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductoService
{
    protected $productoAutomotrizRepository;
    protected $productoDespensaRepository;

    public function __construct(
        ProductoAutomotrizRepositoryInterface $productoAutomotrizRepository,
        ProductoDespensaRepositoryInterface $productoDespensaRepository
    ) {
        $this->productoAutomotrizRepository = $productoAutomotrizRepository;
        $this->productoDespensaRepository = $productoDespensaRepository;
    }

    /**
     * Get all products (both automotriz and despensa) with unified format
     */
    public function getAllProducts(): array
    {
        $automotrices = $this->productoAutomotrizRepository->getAll()->map(function($producto) {
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

        $despensa = $this->productoDespensaRepository->getAll()->map(function($producto) {
            return [
                'id' => $producto->producto_despensa_id,
                'codigo' => null, // Productos de despensa no tienen código
                'nombre' => $producto->nombre,
                'precio_venta' => $producto->precio_venta,
                'stock' => $producto->stock,
                'descripcion' => $producto->descripcion,
                'activo' => $producto->activo,
                'tipo' => 'despensa'
            ];
        });

        return $automotrices->concat($despensa)->toArray();
    }

    /**
     * Get only automotriz products
     */
    public function getProductosAutomotrices(): Collection
    {
        return $this->productoAutomotrizRepository->getAll();
    }

    /**
     * Get only despensa products
     */
    public function getProductosDespensa(): Collection
    {
        return $this->productoDespensaRepository->getAll();
    }

    /**
     * Get product metrics
     */
    public function getMetricas(): array
    {
        return $this->productoAutomotrizRepository->getMetricas() + 
               $this->productoDespensaRepository->getMetricas();
    }

    /**
     * Create automotriz product
     */
    public function createProductoAutomotriz(array $data): ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->create($data);
    }

    /**
     * Create despensa product
     */
    public function createProductoDespensa(array $data): ProductoDespensa
    {
        return $this->productoDespensaRepository->create($data);
    }

    /**
     * Find automotriz product by ID
     */
    public function findProductoAutomotrizById(int $id): ?ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->findById($id);
    }

    /**
     * Find despensa product by ID
     */
    public function findProductoDespensaById(int $id): ?ProductoDespensa
    {
        return $this->productoDespensaRepository->findById($id);
    }

    /**
     * Update automotriz product
     */
    public function updateProductoAutomotriz(int $id, array $data): ?ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->update($id, $data);
    }

    /**
     * Update despensa product
     */
    public function updateProductoDespensa(int $id, array $data): ?ProductoDespensa
    {
        return $this->productoDespensaRepository->update($id, $data);
    }

    /**
     * Delete automotriz product
     */
    public function deleteProductoAutomotriz(int $id): bool
    {
        return $this->productoAutomotrizRepository->delete($id);
    }

    /**
     * Delete despensa product
     */
    public function deleteProductoDespensa(int $id): bool
    {
        return $this->productoDespensaRepository->delete($id);
    }

    /**
     * Restore automotriz product
     */
    public function restoreProductoAutomotriz(int $id): bool
    {
        return $this->productoAutomotrizRepository->restore($id);
    }

    /**
     * Restore despensa product
     */
    public function restoreProductoDespensa(int $id): bool
    {
        return $this->productoDespensaRepository->restore($id);
    }

    /**
     * Get trashed automotriz products
     */
    public function getTrashedProductosAutomotrices(): Collection
    {
        return $this->productoAutomotrizRepository->getTrashed();
    }

    /**
     * Get trashed despensa products
     */
    public function getTrashedProductosDespensa(): Collection
    {
        return $this->productoDespensaRepository->getTrashed();
    }

    /**
     * Update stock for automotriz product
     */
    public function updateStockAutomotriz(int $id, int $newStock): ?ProductoAutomotriz
    {
        return $this->productoAutomotrizRepository->updateStock($id, $newStock);
    }

    /**
     * Update stock for despensa product
     */
    public function updateStockDespensa(int $id, int $newStock): ?ProductoDespensa
    {
        return $this->productoDespensaRepository->updateStock($id, $newStock);
    }
}
