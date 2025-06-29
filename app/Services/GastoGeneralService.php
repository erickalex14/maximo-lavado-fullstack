<?php

namespace App\Services;

use App\Contracts\GastoGeneralRepositoryInterface;
use App\Models\GastoGeneral;
use Illuminate\Database\Eloquent\Collection;

class GastoGeneralService
{
    protected $gastoGeneralRepository;

    public function __construct(GastoGeneralRepositoryInterface $gastoGeneralRepository)
    {
        $this->gastoGeneralRepository = $gastoGeneralRepository;
    }

    public function getAllGastosGenerales(): Collection
    {
        return $this->gastoGeneralRepository->getAll();
    }

    public function findGastoGeneralById(int $id): ?GastoGeneral
    {
        return $this->gastoGeneralRepository->findById($id);
    }

    public function createGastoGeneral(array $data): GastoGeneral
    {
        return $this->gastoGeneralRepository->create($data);
    }

    public function updateGastoGeneral(int $id, array $data): ?GastoGeneral
    {
        return $this->gastoGeneralRepository->update($id, $data);
    }

    public function deleteGastoGeneral(int $id): bool
    {
        return $this->gastoGeneralRepository->delete($id);
    }

    public function restoreGastoGeneral(int $id): bool
    {
        return $this->gastoGeneralRepository->restore($id);
    }

    public function getTrashedGastosGenerales(): Collection
    {
        return $this->gastoGeneralRepository->getTrashed();
    }

    public function getGastosGeneralesByFechaRange(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->gastoGeneralRepository->getByFechaRange($fechaInicio, $fechaFin);
    }

    public function getTotalGastosByPeriodo(string $fechaInicio, string $fechaFin): float
    {
        return $this->gastoGeneralRepository->getTotalGastosByPeriodo($fechaInicio, $fechaFin);
    }

    public function getGastosByMes(int $año, int $mes): Collection
    {
        return $this->gastoGeneralRepository->getGastosByMes($año, $mes);
    }

    public function getMetricas(): array
    {
        $hoy = now()->format('Y-m-d');
        $primerDiaMes = now()->startOfMonth()->format('Y-m-d');
        $ultimoDiaMes = now()->endOfMonth()->format('Y-m-d');

        return [
            'total_gastos_hoy' => $this->gastoGeneralRepository->getByFechaRange($hoy, $hoy)->sum('monto'),
            'total_gastos_mes' => $this->gastoGeneralRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->sum('monto'),
            'promedio_gasto_diario' => $this->gastoGeneralRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->avg('monto'),
            'cantidad_gastos_mes' => $this->gastoGeneralRepository->getByFechaRange($primerDiaMes, $ultimoDiaMes)->count(),
        ];
    }
}
