<?php

namespace App\Services;

use App\Contracts\BalanceRepositoryInterface;
use Carbon\Carbon;

class BalanceService
{
    protected $balanceRepository;

    public function __construct(BalanceRepositoryInterface $balanceRepository)
    {
        $this->balanceRepository = $balanceRepository;
    }

    public function getBalanceGeneral(array $params): array
    {
        $fechaInicio = $params['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $params['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        return $this->balanceRepository->getBalanceGeneral($fechaInicio, $fechaFin);
    }

    public function getBalancePorCategoria(array $params): array
    {
        $fechaInicio = $params['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $params['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        return $this->balanceRepository->getBalancePorCategoria($fechaInicio, $fechaFin);
    }

    public function getBalancePorMes(int $año): array
    {
        return $this->balanceRepository->getBalancePorMes($año);
    }

    public function getBalancePorTrimestre(int $año): array
    {
        return $this->balanceRepository->getBalancePorTrimestre($año);
    }

    public function getBalanceAnual(int $año): array
    {
        return $this->balanceRepository->getBalanceAnual($año);
    }

    public function getFlujoCaja(array $params): array
    {
        $fechaInicio = $params['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $params['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        return $this->balanceRepository->getFlujoCaja($fechaInicio, $fechaFin);
    }

    public function getIndicadoresFinancieros(array $params): array
    {
        $fechaInicio = $params['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $params['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        return $this->balanceRepository->getIndicadoresFinancieros($fechaInicio, $fechaFin);
    }

    public function getComparativoMensual(int $año): array
    {
        return $this->balanceRepository->getComparativoMensual($año);
    }

    public function getBalanceProjection(array $params): array
    {
        $fechaInicio = $params['fecha_inicio'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $params['fecha_fin'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $diasProyeccion = $params['dias_proyeccion'] ?? 30;

        return $this->balanceRepository->getBalanceProjection($fechaInicio, $fechaFin, $diasProyeccion);
    }

    public function getResumenCompleto(array $params): array
    {
        $año = $params['año'] ?? Carbon::now()->year;
        
        return [
            'balance_general' => $this->getBalanceGeneral($params),
            'balance_categorias' => $this->getBalancePorCategoria($params),
            'balance_anual' => $this->getBalanceAnual($año),
            'indicadores' => $this->getIndicadoresFinancieros($params),
            'proyeccion' => $this->getBalanceProjection($params)
        ];
    }
}
