<?php

namespace App\Repositories;

use App\Contracts\LavadoRepositoryInterface;
use App\Models\Lavado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class LavadoRepository implements LavadoRepositoryInterface
{
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Lavado::with(['vehiculo.cliente', 'empleado']);

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('vehiculo.cliente', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            })->orWhereHas('vehiculo', function ($q) use ($search) {
                $q->where('matricula', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['vehiculo_id'])) {
            $query->where('vehiculo_id', $filters['vehiculo_id']);
        }

        if (!empty($filters['empleado_id'])) {
            $query->where('empleado_id', $filters['empleado_id']);
        }

        if (!empty($filters['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $filters['fecha_inicio']);
        }

        if (!empty($filters['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $filters['fecha_fin']);
        }

        return $query->orderBy('fecha', 'desc')->paginate($perPage);
    }

    public function getAll(): Collection
    {
        return Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->orderBy('fecha', 'desc')
                    ->get();
    }

    public function findById(int $id): ?Lavado
    {
        return Lavado::with(['vehiculo.cliente', 'empleado'])->find($id);
    }

    public function create(array $data): Lavado
    {
        return Lavado::create($data);
    }

    public function update(int $id, array $data): Lavado
    {
        $lavado = Lavado::findOrFail($id);
        $lavado->update($data);
        return $lavado->fresh(['vehiculo.cliente', 'empleado']);
    }

    public function delete(int $id): bool
    {
        $lavado = Lavado::findOrFail($id);
        return $lavado->delete();
    }

    public function getByCliente(int $clienteId): Collection
    {
        return Lavado::with(['empleado', 'vehiculo'])
                    ->whereHas('vehiculo', function($q) use ($clienteId) {
                        $q->where('cliente_id', $clienteId);
                    })
                    ->orderBy('fecha', 'desc')
                    ->get();
    }

    public function getByEmpleado(int $empleadoId): Collection
    {
        return Lavado::with(['vehiculo.cliente'])
                    ->where('empleado_id', $empleadoId)
                    ->orderBy('fecha', 'desc')
                    ->get();
    }

    public function getByDateRange(string $fechaInicio, string $fechaFin): Collection
    {
        return Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->orderBy('fecha', 'desc')
                    ->get();
    }

    public function getStats(): array
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $totalHoy = Lavado::whereDate('fecha', $hoy)->count();
        $totalMes = Lavado::whereBetween('fecha', [$inicioMes, $finMes])->count();
        $ingresosMes = Lavado::whereBetween('fecha', [$inicioMes, $finMes])->sum('precio');
        $promedioDiario = $totalMes > 0 ? $ingresosMes / $hoy->day : 0;

        return [
            'total_hoy' => $totalHoy,
            'total_mes' => $totalMes,
            'ingresos_mes' => $ingresosMes,
            'promedio_diario' => $promedioDiario
        ];
    }

    public function getRecientes(int $limit = 10): Collection
    {
        return Lavado::with(['vehiculo.cliente', 'empleado'])
                    ->orderBy('fecha', 'desc')
                    ->limit($limit)
                    ->get();
    }
}
