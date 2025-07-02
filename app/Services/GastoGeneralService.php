<?php

namespace App\Services;

use App\Contracts\GastoGeneralRepositoryInterface;
use App\Contracts\EgresoRepositoryInterface;
use App\Models\GastoGeneral;
use App\Models\Egreso;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GastoGeneralService
{
    protected $gastoGeneralRepository;
    protected $egresoRepository;

    public function __construct(
        GastoGeneralRepositoryInterface $gastoGeneralRepository,
        EgresoRepositoryInterface $egresoRepository
    ) {
        $this->gastoGeneralRepository = $gastoGeneralRepository;
        $this->egresoRepository = $egresoRepository;
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
        return DB::transaction(function () use ($data) {
            // Crear el gasto general
            $gastoGeneral = $this->gastoGeneralRepository->create($data);
            
            // Crear el egreso asociado automáticamente
            $egresoData = [
                'fecha' => $data['fecha'],
                'tipo' => 'gasto_general',
                'referencia_id' => $gastoGeneral->gasto_general_id,
                'monto' => $data['monto'],
                'descripcion' => 'Egreso generado automáticamente por gasto general: ' . $data['nombre']
            ];
            
            $this->egresoRepository->create($egresoData);
            
            return $gastoGeneral;
        });
    }

    public function updateGastoGeneral(int $id, array $data): ?GastoGeneral
    {
        return DB::transaction(function () use ($id, $data) {
            // Actualizar el gasto general
            $gastoGeneral = $this->gastoGeneralRepository->update($id, $data);
            
            if ($gastoGeneral) {
                // Buscar y actualizar el egreso asociado
                $egreso = $this->egresoRepository->findByTipoAndReferencia('gasto_general', $id);
                
                if ($egreso) {
                    $egresoData = [
                        'fecha' => $data['fecha'] ?? $egreso->fecha,
                        'monto' => $data['monto'] ?? $egreso->monto,
                        'descripcion' => 'Egreso actualizado automáticamente por gasto general: ' . ($data['nombre'] ?? $gastoGeneral->nombre)
                    ];
                    
                    $this->egresoRepository->update($egreso->egreso_id, $egresoData);
                }
            }
            
            return $gastoGeneral;
        });
    }

    public function deleteGastoGeneral(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            // Eliminar (soft delete) el egreso asociado primero
            $egreso = $this->egresoRepository->findByTipoAndReferencia('gasto_general', $id);
            if ($egreso) {
                $this->egresoRepository->delete($egreso->egreso_id);
            }
            
            // Eliminar (soft delete) el gasto general
            return $this->gastoGeneralRepository->delete($id);
        });
    }

    public function restoreGastoGeneral(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            // Restaurar el gasto general
            $restored = $this->gastoGeneralRepository->restore($id);
            
            if ($restored) {
                // Restaurar el egreso asociado también
                $egreso = $this->egresoRepository->findTrashedByTipoAndReferencia('gasto_general', $id);
                if ($egreso) {
                    $this->egresoRepository->restore($egreso->egreso_id);
                }
            }
            
            return $restored;
        });
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
