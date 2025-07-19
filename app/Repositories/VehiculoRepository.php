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
        $query = Vehiculo::with(['cliente', 'tipoVehiculo']);

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('matricula', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                      $clienteQuery->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tipoVehiculo', function ($tipoQuery) use ($search) {
                      $tipoQuery->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['cliente_id'])) {
            $query->where('cliente_id', $filters['cliente_id']);
        }

        // ✅ ACTUALIZADO: Usar tipo_vehiculo_id en lugar de tipo directo
        if (!empty($filters['tipo_vehiculo_id'])) {
            $query->where('tipo_vehiculo_id', $filters['tipo_vehiculo_id']);
        }

        // 🔄 COMPATIBILIDAD: Mantener filtro por tipo legacy mientras se migra
        if (!empty($filters['tipo'])) {
            $query->whereHas('tipoVehiculo', function ($tipoQuery) use ($filters) {
                $tipoQuery->where('nombre', $filters['tipo']);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return Vehiculo::with(['cliente', 'tipoVehiculo'])->orderBy('matricula')->get();
    }

    public function findById(int $id): ?Vehiculo
    {
        return Vehiculo::with(['cliente', 'tipoVehiculo'])->find($id);
    }

    public function create(array $data): Vehiculo
    {
        return \DB::transaction(function () use ($data) {
            // Validar matrícula única (si se proporciona)
            if (isset($data['matricula']) && !empty($data['matricula'])) {
                if ($this->existsByMatricula($data['matricula'])) {
                    throw new \Exception('Ya existe un vehículo con esta matrícula');
                }
            }

            return Vehiculo::create($data);
        });
    }

    public function update(int $id, array $data): Vehiculo
    {
        return \DB::transaction(function () use ($id, $data) {
            $vehiculo = Vehiculo::findOrFail($id);
            
            // Validar matrícula única si se está actualizando y es diferente
            if (isset($data['matricula']) && !empty($data['matricula']) && $data['matricula'] !== $vehiculo->matricula) {
                if ($this->existsByMatricula($data['matricula'], $id)) {
                    throw new \Exception('Ya existe un vehículo con esta matrícula');
                }
            }
            
            $vehiculo->update($data);
            return $vehiculo->fresh(['cliente']);
        });
    }

    public function delete(int $id): bool
    {
        return \DB::transaction(function () use ($id) {
            $vehiculo = Vehiculo::findOrFail($id);
            return $vehiculo->delete();
        });
    }

    /**
     * Restaurar vehículo eliminado lógicamente
     */
    public function restore(int $id): bool
    {
        return \DB::transaction(function () use ($id) {
            $vehiculo = Vehiculo::onlyTrashed()->findOrFail($id);
            return $vehiculo->restore();
        });
    }

    /**
     * Obtener vehículos eliminados lógicamente
     */
    public function getTrashed(): Collection
    {
        return Vehiculo::onlyTrashed()
            ->with(['cliente'])
            ->orderBy('deleted_at', 'desc')
            ->get();
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
        
        // ✅ ACTUALIZADO: Obtener estadísticas por tipo de vehículo usando relación
        $porTipo = Vehiculo::with('tipoVehiculo')
                          ->get()
                          ->groupBy('tipoVehiculo.nombre')
                          ->map(function ($grupo) {
                              return $grupo->count();
                          })
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
