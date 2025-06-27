<?php

namespace App\Contracts;

use App\Models\ProductoDespensa;
use Illuminate\Database\Eloquent\Collection;

interface ProductoDespensaRepositoryInterface
{
    public function getAll(): Collection;
    
    public function findById(int $id): ?ProductoDespensa;
    
    public function create(array $data): ProductoDespensa;
    
    public function update(int $id, array $data): ?ProductoDespensa;
    
    public function delete(int $id): bool;
    
    public function getActiveProducts(): Collection;
    
    public function getInactiveProducts(): Collection;
    
    public function updateStock(int $id, int $newStock): ?ProductoDespensa;
    
    public function toggleActive(int $id): ?ProductoDespensa;
}
