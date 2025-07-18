<?php

namespace App\Repositories;

use App\Contracts\TipoVehiculoRepositoryInterface;
use App\Models\TipoVehiculo;
use Illuminate\Database\Eloquent\Collection;

class TipoVehiculoRepository implements TipoVehiculoRepositoryInterface
{
    public function getAll(): Collection
    {
        return TipoVehiculo::orderBy('nombre')->get();
    }

    public function getAllWithTrashed(): Collection
    {
        return TipoVehiculo::withTrashed()->orderBy('nombre')->get();
    }

    public function getOnlyTrashed(): Collection
    {
        return TipoVehiculo::onlyTrashed()->orderBy('nombre')->get();
    }

    public function findById(int $id): ?TipoVehiculo
    {
        return TipoVehiculo::find($id);
    }

    public function findByIdWithTrashed(int $id): ?TipoVehiculo
    {
        return TipoVehiculo::withTrashed()->find($id);
    }

    public function create(array $data): TipoVehiculo
    {
        return TipoVehiculo::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $tipoVehiculo = $this->findById($id);
        
        if (!$tipoVehiculo) {
            return false;
        }

        return $tipoVehiculo->update($data);
    }

    public function delete(int $id): bool
    {
        $tipoVehiculo = $this->findById($id);
        
        if (!$tipoVehiculo) {
            return false;
        }

        return $tipoVehiculo->delete();
    }

    public function restore(int $id): bool
    {
        $tipoVehiculo = $this->findByIdWithTrashed($id);
        
        if (!$tipoVehiculo || !$tipoVehiculo->trashed()) {
            return false;
        }

        return $tipoVehiculo->restore();
    }

    public function forceDelete(int $id): bool
    {
        $tipoVehiculo = $this->findByIdWithTrashed($id);
        
        if (!$tipoVehiculo) {
            return false;
        }

        return $tipoVehiculo->forceDelete();
    }

    public function findByName(string $nombre): Collection
    {
        return TipoVehiculo::where('nombre', 'ILIKE', "%{$nombre}%")
            ->orderBy('nombre')
            ->get();
    }

    public function getActivosParaServicios(): Collection
    {
        return TipoVehiculo::where('activo', true)
            ->orderBy('nombre')
            ->get();
    }
}
