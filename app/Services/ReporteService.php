<?php

namespace App\Services;

use App\Contracts\ReporteRepositoryInterface;
use Carbon\Carbon;

class ReporteService
{
    protected $reporteRepository;

    public function __construct(ReporteRepositoryInterface $reporteRepository)
    {
        $this->reporteRepository = $reporteRepository;
    }

    public function getReporteVentas(string $fechaInicio, string $fechaFin, string $formato = 'json'): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        $reporte = $this->reporteRepository->getReporteVentas($fechaInicio, $fechaFin);
        
        return $this->formatearReporte($reporte, 'ventas', $fechaInicio, $fechaFin, $formato);
    }

    public function getReporteLavados(string $fechaInicio, string $fechaFin, string $formato = 'json'): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        $reporte = $this->reporteRepository->getReporteLavados($fechaInicio, $fechaFin);
        
        return $this->formatearReporte($reporte, 'lavados', $fechaInicio, $fechaFin, $formato);
    }

    public function getReporteIngresos(string $fechaInicio, string $fechaFin, string $formato = 'json'): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        $reporte = $this->reporteRepository->getReporteIngresos($fechaInicio, $fechaFin);
        
        return $this->formatearReporte($reporte, 'ingresos', $fechaInicio, $fechaFin, $formato);
    }

    public function getReporteEgresos(string $fechaInicio, string $fechaFin, string $formato = 'json'): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        $reporte = $this->reporteRepository->getReporteEgresos($fechaInicio, $fechaFin);
        
        return $this->formatearReporte($reporte, 'egresos', $fechaInicio, $fechaFin, $formato);
    }

    public function getReporteFacturas(string $fechaInicio, string $fechaFin, string $formato = 'json'): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        $reporte = $this->reporteRepository->getReporteFacturas($fechaInicio, $fechaFin);
        
        return $this->formatearReporte($reporte, 'facturas', $fechaInicio, $fechaFin, $formato);
    }

    public function getReporteEmpleados(string $fechaInicio, string $fechaFin, string $formato = 'json'): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        $reporte = $this->reporteRepository->getReporteEmpleados($fechaInicio, $fechaFin);
        
        return $this->formatearReporte($reporte, 'empleados', $fechaInicio, $fechaFin, $formato);
    }

    public function getReporteProductos(string $formato = 'json'): array
    {
        $reporte = $this->reporteRepository->getReporteProductos();
        
        return $this->formatearReporte($reporte, 'productos', null, null, $formato);
    }

    public function getReporteClientes(string $formato = 'json'): array
    {
        $reporte = $this->reporteRepository->getReporteClientes();
        
        return $this->formatearReporte($reporte, 'clientes', null, null, $formato);
    }

    public function getReporteFinanciero(string $fechaInicio, string $fechaFin, string $formato = 'json'): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        $reporte = $this->reporteRepository->getReporteFinanciero($fechaInicio, $fechaFin);
        
        return $this->formatearReporte($reporte, 'financiero', $fechaInicio, $fechaFin, $formato);
    }

    public function getReporteBalance(string $fechaInicio, string $fechaFin, string $formato = 'json'): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        $reporte = $this->reporteRepository->getReporteBalance($fechaInicio, $fechaFin);
        
        return $this->formatearReporte($reporte, 'balance', $fechaInicio, $fechaFin, $formato);
    }

    public function getReporteCompleto(string $fechaInicio, string $fechaFin): array
    {
        $this->validarFechas($fechaInicio, $fechaFin);
        
        return [
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'dias' => Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1
            ],
            'ventas' => $this->reporteRepository->getReporteVentas($fechaInicio, $fechaFin),
            'lavados' => $this->reporteRepository->getReporteLavados($fechaInicio, $fechaFin),
            'ingresos' => $this->reporteRepository->getReporteIngresos($fechaInicio, $fechaFin),
            'egresos' => $this->reporteRepository->getReporteEgresos($fechaInicio, $fechaFin),
            'facturas' => $this->reporteRepository->getReporteFacturas($fechaInicio, $fechaFin),
            'empleados' => $this->reporteRepository->getReporteEmpleados($fechaInicio, $fechaFin),
            'financiero' => $this->reporteRepository->getReporteFinanciero($fechaInicio, $fechaFin)
        ];
    }

    public function getReportesDisponibles(): array
    {
        return [
            'reportes_con_fechas' => [
                'ventas' => 'Reporte de ventas por período',
                'lavados' => 'Reporte de lavados por período',
                'ingresos' => 'Reporte de ingresos por período',
                'egresos' => 'Reporte de egresos por período',
                'facturas' => 'Reporte de facturas por período',
                'empleados' => 'Reporte de rendimiento de empleados',
                'financiero' => 'Reporte financiero completo',
                'balance' => 'Reporte de balance y utilidades',
                'completo' => 'Reporte completo del negocio'
            ],
            'reportes_sin_fechas' => [
                'productos' => 'Reporte de inventario de productos',
                'clientes' => 'Reporte de análisis de clientes'
            ]
        ];
    }

    private function validarFechas(string $fechaInicio, string $fechaFin): void
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);

        if ($inicio->gt($fin)) {
            throw new \InvalidArgumentException('La fecha de inicio no puede ser mayor que la fecha de fin');
        }

        if ($inicio->gt(Carbon::now())) {
            throw new \InvalidArgumentException('La fecha de inicio no puede ser futura');
        }

        if ($inicio->diffInDays($fin) > 365) {
            throw new \InvalidArgumentException('El período no puede ser mayor a 365 días');
        }
    }

    private function formatearReporte(array $reporte, string $tipo, ?string $fechaInicio, ?string $fechaFin, string $formato): array
    {
        $reporteFormateado = [
            'tipo_reporte' => $tipo,
            'formato' => $formato,
            'generado_en' => now()->format('Y-m-d H:i:s'),
            'data' => $reporte
        ];

        if ($fechaInicio && $fechaFin) {
            $reporteFormateado['periodo'] = [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'dias' => Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1
            ];
        }

        return $reporteFormateado;
    }
}
