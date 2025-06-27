<?php

namespace App\Repositories;

use App\Contracts\ClienteRepositoryInterface;
use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClienteRepository implements ClienteRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Cliente::query();

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return Cliente::orderBy('nombre')->get();
    }

    public function findById(int $id): ?Cliente
    {
        return Cliente::find($id);
    }

    public function create(array $data): Cliente
    {
        return Cliente::create($data);
    }

    public function update(int $id, array $data): Cliente
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($data);
        return $cliente->fresh();
    }

    public function delete(int $id): bool
    {
        $cliente = Cliente::findOrFail($id);
        return $cliente->delete();
    }

    public function search(string $term): Collection
    {
        return Cliente::where('nombre', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('telefono', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get();
    }

    public function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        $query = Cliente::where('email', $email);
        
        if ($excludeId) {
            $query->where('cliente_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function existsByTelefono(string $telefono, ?int $excludeId = null): bool
    {
        $query = Cliente::where('telefono', $telefono);
        
        if ($excludeId) {
            $query->where('cliente_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getStats(): array
    {
        $total = Cliente::count();
        $nuevosEsteMes = Cliente::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->count();

        return [
            'total' => $total,
            'nuevos_este_mes' => $nuevosEsteMes
        ];
    }
}
