<?php

namespace App\Contracts;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ClienteRepositoryInterface
{
    /**
     * Obtener todos los clientes paginados
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Obtener todos los clientes
     */
    public function getAll(): Collection;

    /**
     * Obtener cliente por ID
     */
    public function findById(int $id): ?Cliente;

    /**
     * Crear nuevo cliente
     */
    public function create(array $data): Cliente;

    /**
     * Actualizar cliente
     */
    public function update(int $id, array $data): Cliente;

    /**
     * Eliminar cliente
     */
    public function delete(int $id): bool;

    /**
     * Buscar clientes por término de búsqueda
     */
    public function search(string $term): Collection;

    /**
     * Verificar si existe un cliente con el email
     */
    public function existsByEmail(string $email, ?int $excludeId = null): bool;

    /**
     * Verificar si existe un cliente con el teléfono
     */
    public function existsByTelefono(string $telefono, ?int $excludeId = null): bool;

    /**
     * Obtener estadísticas de clientes
     */
    public function getStats(): array;
}
