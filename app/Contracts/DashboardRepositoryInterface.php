<?php

namespace App\Contracts;

interface DashboardRepositoryInterface
{
    public function getMetricasPrincipales(): array;
    public function getActividadReciente(int $limite = 5): array;
    public function getProximasCitas(int $limite = 5): array;
    public function getChartDataSemanal(): array;
    public function getAlertasSistema(): array;
    public function getEstadisticasGenerales(): array;
    public function getIngresosSemanales(): array;
    public function getServiciosPopulares(int $limite = 5): array;
    public function getLavadosRecientes(int $limite = 5): array;
    public function getIndicadoresFinancieros(): array;
    public function getResumenVentas(): array;
    public function getRendimientoEmpleados(): array;
}
