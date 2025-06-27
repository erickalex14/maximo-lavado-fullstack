<?php

namespace App\Contracts;

interface BalanceRepositoryInterface
{
    public function getBalanceGeneral(string $fechaInicio, string $fechaFin): array;
    public function getBalancePorCategoria(string $fechaInicio, string $fechaFin): array;
    public function getBalancePorMes(int $año): array;
    public function getBalancePorTrimestre(int $año): array;
    public function getBalanceAnual(int $año): array;
    public function getFlujoCaja(string $fechaInicio, string $fechaFin): array;
    public function getIndicadoresFinancieros(string $fechaInicio, string $fechaFin): array;
    public function getComparativoMensual(int $año): array;
    public function getBalanceProjection(string $fechaInicio, string $fechaFin, int $diasProyeccion): array;
}
