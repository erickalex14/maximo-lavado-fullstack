<?php

namespace App\Contracts;

use App\Models\ProductoAutomotriz;
use Illuminate\Database\Eloquent\Collection;

interface ProductoAutomotrizRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?ProductoAutomotriz;
    
    public function create(array $data): ProductoAutomotriz;
    
    public function update(int $id, array $data): ?ProductoAutomotriz;
    
    public function delete(int $id): bool;
    
    /**
     * Restaurar producto automotriz eliminado lógicamente
     */
    public function restore(int $id): bool;
    
    /**
     * Obtener productos automotrices eliminados lógicamente
     */
    public function getTrashed(): Collection;
    
    public function getActiveProducts(): Collection;
    
    public function getInactiveProducts(): Collection;
    
    public function findByCodigo(string $codigo): ?ProductoAutomotriz;
    
    public function updateStock(int $id, int $newStock): ?ProductoAutomotriz;
    
    public function toggleActive(int $id): ?ProductoAutomotriz;
}
