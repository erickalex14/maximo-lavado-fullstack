<?php

namespace App\Contracts;

interface ReporteRepositoryInterface
{
    public function getReporteVentas(string $fechaInicio, string $fechaFin): array;
    public function getReporteLavados(string $fechaInicio, string $fechaFin): array;
    public function getReporteIngresos(string $fechaInicio, string $fechaFin): array;
    public function getReporteEgresos(string $fechaInicio, string $fechaFin): array;
    public function getReporteFacturas(string $fechaInicio, string $fechaFin): array;
    public function getReporteEmpleados(string $fechaInicio, string $fechaFin): array;
    public function getReporteProductos(): array;
    public function getReporteClientes(): array;
    public function getReporteFinanciero(string $fechaInicio, string $fechaFin): array;
    public function getReporteBalance(string $fechaInicio, string $fechaFin): array;
}
