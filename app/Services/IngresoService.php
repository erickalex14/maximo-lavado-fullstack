<?php

namespace App\Services;

use App\Contracts\IngresoRepositoryInterface;
use App\Models\Ingreso;
use Illuminate\Database\Eloquent\Collection;

class IngresoService
{
    protected $ingresoRepository;

    public function __construct(IngresoRepositoryInterface $ingresoRepository)
    {
        $this->ingresoRepository = $ingresoRepository;
    }

    public function getAllIngresos(): Collection
    {
        return $this->ingresoRepository->getAll();
    }

    public function findIngresoById(int $id): ?Ingreso
    {
        return $this->ingresoRepository->findById($id);
    }

    public function createIngreso(array $data): Ingreso
    {
        return $this->ingresoRepository->create($data);
    }

    public function updateIngreso(int $id, array $data): ?Ingreso
    {
        return $this->ingresoRepository->update($id, $data);
    }

    public function deleteIngreso(int $id): bool
    {
        return $this->ingresoRepository->delete($id);
    }

    public function restoreIngreso(int $id): bool
    {
        return $this->ingresoRepository->restore($id);
    }

    public function getTrashedIngresos(): Collection
    {
        return $this->ingresoRepository->getTrashed();
    }

    public function getIngresosByTipo(string $tipo): Collection
    {
        return $this->ingresoRepository->getByTipo($tipo);
    }

    public function getIngresosByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->ingresoRepository->getByFechaRange($fechaInicio, $fechaFin);
    }

    public function getTotalIngresosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return $this->ingresoRepository->getTotalIngresosByPeriodo($fechaInicio, $fechaFin);
    }

    public function getIngresosByMes(int $año, int $mes): Collection
    {
        return $this->ingresoRepository->getIngresosByMes($año, $mes);
    }

    public function getMetricas(): array
    {
        $hoy = now()->format('Y-m-d');
        $primerDiaMes = now()->startOfMonth()->format('Y-m-d');
        $ultimoDiaMes = now()->endOfMonth()->format('Y-m-d');

        return [
            'total_ingresos_hoy' => $this->ingresoRepository->getByFechaRange($hoy, $hoy)->sum('monto'),
            'total_ingresos_mes' => $this->ingresoRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->sum('monto'),
            'ingresos_por_tipo' => [
                'lavado' => $this->ingresoRepository->getByTipo('lavado')->sum('monto'),
                'producto_automotriz' => $this->ingresoRepository->getByTipo('producto_automotriz')->sum('monto'),
                'producto_despensa' => $this->ingresoRepository->getByTipo('producto_despensa')->sum('monto'),
            ],
            'promedio_diario_mes' => $this->ingresoRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->avg('monto'),
        ];
    }
}
