<?php

namespace App\Repositories;

use App\Contracts\VehiculoRepositoryInterface;
use App\Models\Vehiculo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VehiculoRepository implements VehiculoRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Vehiculo::with(['cliente']);

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('matricula', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                      $clienteQuery->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }

        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return Vehiculo::with(['cliente'])->orderBy('matricula')->get();
    }

    public function findById(int $id): ?Vehiculo
    {
        return Vehiculo::with(['cliente'])->find($id);
    }

    public function create(array $data): Vehiculo
    {
        return Vehiculo::create($data);
    }

    public function update(int $id, array $data): Vehiculo
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->update($data);
        return $vehiculo->fresh(['cliente']);
    }

    public function delete(int $id): bool
    {
        $vehiculo = Vehiculo::findOrFail($id);
        return $vehiculo->delete();
    }

    public function getByCliente(int $clienteId): Collection
    {
        return Vehiculo::where('cliente_id', $clienteId)
                      ->orderBy('matricula')
                      ->get();
    }

    public function existsByMatricula(string $matricula, ?int $excludeId = null): bool
    {
        $query = Vehiculo::where('matricula', $matricula);
        
        if ($excludeId) {
            $query->where('vehiculo_id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getStats(): array
    {
        $total = Vehiculo::count();
        $porTipo = Vehiculo::selectRaw('tipo, COUNT(*) as total')
                          ->groupBy('tipo')
                          ->pluck('total', 'tipo')
                          ->toArray();

        $nuevosEsteMes = Vehiculo::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->count();

        return [
            'total' => $total,
            'por_tipo' => $porTipo,
            'nuevos_este_mes' => $nuevosEsteMes
        ];
    }
}
