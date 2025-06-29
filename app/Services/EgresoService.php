<?php

namespace App\Services;

use App\Contracts\EgresoRepositoryInterface;
use App\Models\Egreso;
use Illuminate\Database\Eloquent\Collection;

class EgresoService
{
    protected $egresoRepository;

    public function __construct(EgresoRepositoryInterface $egresoRepository)
    {
        $this->egresoRepository = $egresoRepository;
    }

    public function getAllEgresos(): Collection
    {
        return $this->egresoRepository->getAll();
    }

    public function findEgresoById(int $id): ?Egreso
    {
        return $this->egresoRepository->findById($id);
    }

    public function createEgreso(array $data): Egreso
    {
        return $this->egresoRepository->create($data);
    }

    public function updateEgreso(int $id, array $data): ?Egreso
    {
        return $this->egresoRepository->update($id, $data);
    }

    public function deleteEgreso(int $id): bool
    {
        return $this->egresoRepository->delete($id);
    }

    public function restoreEgreso(int $id): bool
    {
        return $this->egresoRepository->restore($id);
    }

    public function getTrashedEgresos(): Collection
    {
        return $this->egresoRepository->getTrashed();
    }

    public function getEgresosByTipo(string $tipo): Collection
    {
        return $this->egresoRepository->getByTipo($tipo);
    }

    public function getEgresosByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->egresoRepository->getByFechaRange($fechaInicio, $fechaFin);
    }

    public function getTotalEgresosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return $this->egresoRepository->getTotalEgresosByPeriodo($fechaInicio, $fechaFin);
    }

    public function getEgresosByMes(int $año, int $mes): Collection
    {
        return $this->egresoRepository->getEgresosByMes($año, $mes);
    }

    public function getMetricas(): array
    {
        $hoy = now()->format('Y-m-d');
        $primerDiaMes = now()->startOfMonth()->format('Y-m-d');
        $ultimoDiaMes = now()->endOfMonth()->format('Y-m-d');

        return [
            'total_egresos_hoy' => $this->egresoRepository->getByFechaRange($hoy, $hoy)->sum('monto'),
            'total_egresos_mes' => $this->egresoRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->sum('monto'),
            'egresos_por_tipo' => [
                'salario' => $this->egresoRepository->getByTipo('salario')->sum('monto'),
                'proveedor' => $this->egresoRepository->getByTipo('proveedor')->sum('monto'),
                'gasto_general' => $this->egresoRepository->getByTipo('gasto_general')->sum('monto'),
            ],
            'promedio_diario_mes' => $this->egresoRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->avg('monto'),
        ];
    }
}
