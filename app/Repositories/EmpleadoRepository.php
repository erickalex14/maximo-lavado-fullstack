<?php

namespace App\Repositories;

use App\Contracts\EmpleadoRepositoryInterface;
use App\Models\Empleado;
use App\Models\Lavado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class EmpleadoRepository implements EmpleadoRepositoryInterface
{
    protected $model;

    public function __construct(Empleado $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Empleado
    {
        return $this->model->where('empleado_id', $id)->first();
    }

    public function create(array $data): Empleado
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?Empleado
    {
        $empleado = $this->findById($id);
        if ($empleado) {
            $empleado->update($data);
            return $empleado->fresh();
        }
        return null;
    }

    public function delete(int $id): bool
    {
        $empleado = $this->findById($id);
        if ($empleado) {
            return $empleado->delete();
        }
        return false;
    }

    /**
     * Restaurar empleado eliminado lógicamente
     */
    public function restore(int $id): bool
    {
        $empleado = Empleado::onlyTrashed()->find($id);
        if ($empleado) {
            return $empleado->restore();
        }
        return false;
    }

    /**
     * Obtener empleados eliminados lógicamente
     */
    public function getTrashed(): Collection
    {
        return Empleado::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    public function getEmpleadosWithLavados(): Collection
    {
        return $this->model->with('lavados')->get();
    }

    public function countLavadosByEmpleadoAndDate(int $empleadoId, string $fecha): int
    {
        return Lavado::where('empleado_id', $empleadoId)
            ->whereDate('fecha', $fecha)
            ->count();
    }

    public function countLavadosByEmpleadoAndWeek(int $empleadoId, string $fecha): int
    {
        $carbon = Carbon::parse($fecha);
        $start = $carbon->startOfWeek();
        $end = $carbon->endOfWeek();
        
        return Lavado::where('empleado_id', $empleadoId)
            ->whereBetween('fecha', [$start, $end])
            ->count();
    }

    public function countLavadosByEmpleadoAndMonth(int $empleadoId, int $anio, int $mes): int
    {
        return Lavado::where('empleado_id', $empleadoId)
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->count();
    }
}
