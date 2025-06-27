<?php

namespace App\Services;

use App\Contracts\DashboardRepositoryInterface;

class DashboardService
{
    protected $dashboardRepository;

    public function __construct(DashboardRepositoryInterface $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function getDashboardData(): array
    {
        return [
            'metricas' => $this->dashboardRepository->getMetricasPrincipales(),
            'actividad_reciente' => $this->dashboardRepository->getActividadReciente(),
            'proximas_citas' => $this->dashboardRepository->getProximasCitas(),
            'alertas' => $this->dashboardRepository->getAlertasSistema(),
            'chart_data' => $this->dashboardRepository->getChartDataSemanal()
        ];
    }

    public function getMetricas(): array
    {
        return $this->dashboardRepository->getMetricasPrincipales();
    }

    public function getActividadReciente(int $limite = 5): array
    {
        return $this->dashboardRepository->getActividadReciente($limite);
    }

    public function getProximasCitas(int $limite = 5): array
    {
        return $this->dashboardRepository->getProximasCitas($limite);
    }

    public function getChartData(): array
    {
        return $this->dashboardRepository->getChartDataSemanal();
    }

    public function getAlertas(): array
    {
        return $this->dashboardRepository->getAlertasSistema();
    }

    public function getEstadisticasGenerales(): array
    {
        return $this->dashboardRepository->getEstadisticasGenerales();
    }

    public function getAnalisisFinanciero(): array
    {
        return [
            'indicadores' => $this->dashboardRepository->getIndicadoresFinancieros(),
            'ingresos_semanales' => $this->dashboardRepository->getIngresosSemanales(),
            'resumen_ventas' => $this->dashboardRepository->getResumenVentas()
        ];
    }

    public function getRendimientoOperativo(): array
    {
        return [
            'servicios_populares' => $this->dashboardRepository->getServiciosPopulares(),
            'lavados_recientes' => $this->dashboardRepository->getLavadosRecientes(),
            'rendimiento_empleados' => $this->dashboardRepository->getRendimientoEmpleados()
        ];
    }

    public function getResumenCompleto(): array
    {
        return [
            'dashboard' => $this->getDashboardData(),
            'estadisticas' => $this->getEstadisticasGenerales(),
            'analisis_financiero' => $this->getAnalisisFinanciero(),
            'rendimiento_operativo' => $this->getRendimientoOperativo()
        ];
    }
}
