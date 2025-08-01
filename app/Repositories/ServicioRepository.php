<?php

namespace App\Repositories;

use App\Contracts\ServicioRepositoryInterface;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ServicioRepository implements ServicioRepositoryInterface
{
    /**
     * Obtener todos los servicios activos
     */
    public function getAll(): Collection
    {
        return Servicio::all();
    }

    /**
     * Obtener todos incluyendo eliminados (soft deletes)
     */
    public function getAllWithTrashed(): Collection
    {
        return Servicio::withTrashed()->get();
    }

    /**
     * Obtener solo los eliminados (soft deletes)
     */
    public function getOnlyTrashed(): Collection
    {
        return Servicio::onlyTrashed()->get();
    }

    /**
     * Buscar servicio por ID
     */
    public function findById(int $id): ?Servicio
    {
        return Servicio::find($id);
    }

    /**
     * Buscar servicio por ID incluyendo eliminados
     */
    public function findByIdWithTrashed(int $id): ?Servicio
    {
        return Servicio::withTrashed()->find($id);
    }

    /**
     * Crear nuevo servicio
     */
    public function create(array $data): Servicio
    {
        return DB::transaction(function () use ($data) {
            return Servicio::create($data);
        });
    }

    /**
     * Actualizar servicio
     */
    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $servicio = Servicio::find($id);
            if ($servicio) {
                return $servicio->update($data);
            }
            return false;
        });
    }

    /**
     * Eliminar servicio (soft delete)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $servicio = Servicio::find($id);
            return $servicio ? $servicio->delete() : false;
        });
    }

    /**
     * Restaurar servicio eliminado
     */
    public function restore(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $servicio = Servicio::onlyTrashed()->find($id);
            return $servicio ? $servicio->restore() : false;
        });
    }

    /**
     * Eliminar permanentemente
     */
    public function forceDelete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $servicio = Servicio::withTrashed()->find($id);
            return $servicio ? $servicio->forceDelete() : false;
        });
    }

    /**
     * Obtener servicios activos
     */
    public function getActiveServices(): Collection
    {
        return Servicio::where('activo', true)->get();
    }

    /**
     * Obtener servicios inactivos
     */
    public function getInactiveServices(): Collection
    {
        return Servicio::where('activo', false)->get();
    }

    /**
     * Buscar servicios por nombre
     */
    public function findByName(string $nombre): Collection
    {
        return Servicio::where('nombre', 'ILIKE', "%{$nombre}%")->get();
    }

    /**
     * Buscar servicio por nombre
     */
    public function findByNombre(string $nombre): ?Servicio
    {
        return Servicio::where('nombre', $nombre)->first();
    }

    /**
     * Verificar si existe un servicio con el nombre
     */
    public function existsByNombre(string $nombre, ?int $excludeId = null): bool
    {
        $query = Servicio::where('nombre', $nombre);
        
        if ($excludeId) {
            $query->where('servicio_id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Obtener servicios por tipo de vehículo
     */
    public function getByTipoVehiculo(int $tipoVehiculoId): Collection
    {
        return Servicio::where('tipo_vehiculo_id', $tipoVehiculoId)
                      ->where('activo', true)
                      ->get();
    }

    /**
     * Obtener servicios activos para ventas
     */
    public function getActivosParaVentas(): Collection
    {
        return Servicio::where('activo', true)
                      ->with(['tipoVehiculo'])
                      ->orderBy('nombre')
                      ->get();
    }

    /**
     * Obtener servicios con precios por tipo de vehículo
     */
    public function getServiciosConPrecios(): Collection
    {
        return Servicio::with(['tipoVehiculo'])
                      ->where('activo', true)
                      ->orderBy('tipo_vehiculo_id')
                      ->orderBy('nombre')
                      ->get();
    }

    /**
     * Buscar servicios por tipo de vehículo
     */
    public function findByTipoVehiculo(int $tipoVehiculoId): Collection
    {
        return $this->getByTipoVehiculo($tipoVehiculoId);
    }

    /**
     * Actualizar precio de servicio para un tipo de vehículo
     */
    public function updatePrecio(int $servicioId, int $tipoVehiculoId, float $precio): bool
    {
        return DB::transaction(function () use ($servicioId, $precio) {
            $servicio = Servicio::find($servicioId);
            if (!$servicio) {
                return false;
            }

            return $servicio->update(['precio_base' => $precio]);
        });
    }

    /**
     * Toggle estado activo del servicio
     */
    public function toggleActive(int $id): ?Servicio
    {
        return DB::transaction(function () use ($id) {
            $servicio = Servicio::find($id);
            if ($servicio) {
                $servicio->update(['activo' => !$servicio->activo]);
                return $servicio->fresh();
            }
            return null;
        });
    }

    /**
     * Obtener estadísticas de servicios
     */
    public function getStats(): array
    {
        return [
            'total' => Servicio::count(),
            'activos' => Servicio::where('activo', true)->count(),
            'inactivos' => Servicio::where('activo', false)->count(),
            'eliminados' => Servicio::onlyTrashed()->count(),
        ];
    }

    /**
     * Buscar servicios por término
     */
    public function search(string $term): Collection
    {
        return Servicio::where(function ($query) use ($term) {
            $query->where('nombre', 'ILIKE', "%{$term}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$term}%");
        })->get();
    }

    /**
     * Obtener servicios paginados
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Servicio::with(['tipoVehiculo']);

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        if (isset($filters['activo']) && $filters['activo'] !== null) {
            $query->where('activo', (bool) $filters['activo']);
        }

        if (!empty($filters['tipo_vehiculo_id'])) {
            $query->where('tipo_vehiculo_id', $filters['tipo_vehiculo_id']);
        }

        return $query->orderBy('tipo_vehiculo_id')->orderBy('nombre')->paginate($perPage);
    }

    /**
     * Obtener precio de servicio para tipo de vehículo específico
     */
    public function getPrecioParaTipoVehiculo(int $servicioId, int $tipoVehiculoId): ?float
    {
        $servicio = Servicio::where('servicio_id', $servicioId)
                           ->where('tipo_vehiculo_id', $tipoVehiculoId)
                           ->first();

        return $servicio ? $servicio->precio_base : null;
    }
}
