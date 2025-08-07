<?php

namespace App\Repositories;

use App\Contracts\BalanceRepositoryInterface;
use App\Models\Ingreso;
use App\Models\Egreso;
use App\Models\FacturaElectronica;
use App\Models\Lavado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceRepository implements BalanceRepositoryInterface
{
    public function getBalanceGeneral(string $fechaInicio, string $fechaFin): array
    {
        $totalIngresos = Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
        $totalEgresos = Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
        $totalFacturas = FacturaElectronica::whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio, $fechaFin])->sum('total');
        $totalLavados = Lavado::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('precio');

        return [
            'ingresos' => $totalIngresos,
            'egresos' => $totalEgresos,
            'facturas' => $totalFacturas,
            'lavados' => $totalLavados,
            'balance_neto' => ($totalIngresos + $totalFacturas + $totalLavados) - $totalEgresos,
            'periodo' => ['desde' => $fechaInicio, 'hasta' => $fechaFin]
        ];
    }

    public function getBalancePorCategoria(string $fechaInicio, string $fechaFin): array
    {
        $ingresosPorTipo = Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->select('tipo', DB::raw('SUM(monto) as total'))
            ->groupBy('tipo')
            ->get()
            ->keyBy('tipo')
            ->map(fn($item) => $item->total);

        $egresosPorTipo = Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->select('tipo', DB::raw('SUM(monto) as total'))
            ->groupBy('tipo')
            ->get()
            ->keyBy('tipo')
            ->map(fn($item) => $item->total);

        $facturas = FacturaElectronica::whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio, $fechaFin])->sum('total');
        $lavados = Lavado::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('precio');

        return [
            'ingresos_por_categoria' => $ingresosPorTipo,
            'egresos_por_categoria' => $egresosPorTipo,
            'ventas' => [
                'facturas' => $facturas,
                'lavados' => $lavados
            ]
        ];
    }

    public function getBalancePorMes(int $año): array
    {
        $meses = [];
        
        for ($mes = 1; $mes <= 12; $mes++) {
            $fechaInicio = Carbon::create($año, $mes, 1)->startOfMonth();
            $fechaFin = Carbon::create($año, $mes, 1)->endOfMonth();

            $ingresos = Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
            $egresos = Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
            $facturas = FacturaElectronica::whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])->sum('total');
            $lavados = Lavado::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('precio');

            $meses[$mes] = [
                'mes' => $fechaInicio->format('M'),
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'facturas' => $facturas,
                'lavados' => $lavados,
                'balance' => ($ingresos + $facturas + $lavados) - $egresos
            ];
        }

        return $meses;
    }

    public function getBalancePorTrimestre(int $año): array
    {
        $trimestres = [];
        
        for ($trimestre = 1; $trimestre <= 4; $trimestre++) {
            $mesInicio = ($trimestre - 1) * 3 + 1;
            $mesFin = $trimestre * 3;
            
            $fechaInicio = Carbon::create($año, $mesInicio, 1)->startOfMonth();
            $fechaFin = Carbon::create($año, $mesFin, 1)->endOfMonth();

            $ingresos = Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
            $egresos = Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
            $facturas = FacturaElectronica::whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])->sum('total');
            $lavados = Lavado::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('precio');

            $trimestres[$trimestre] = [
                'trimestre' => "Q{$trimestre}",
                'periodo' => $fechaInicio->format('M') . ' - ' . $fechaFin->format('M'),
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'facturas' => $facturas,
                'lavados' => $lavados,
                'balance' => ($ingresos + $facturas + $lavados) - $egresos
            ];
        }

        return $trimestres;
    }

    public function getBalanceAnual(int $año): array
    {
        $fechaInicio = Carbon::create($año, 1, 1)->startOfYear();
        $fechaFin = Carbon::create($año, 12, 31)->endOfYear();

        $ingresos = Ingreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
        $egresos = Egreso::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('monto');
        $facturas = FacturaElectronica::whereBetween(DB::raw('DATE(created_at)'), [$fechaInicio, $fechaFin])->sum('total');
        $lavados = Lavado::whereBetween('fecha', [$fechaInicio, $fechaFin])->sum('precio');

        return [
            'año' => $año,
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'facturas' => $facturas,
            'lavados' => $lavados,
            'balance_anual' => ($ingresos + $facturas + $lavados) - $egresos,
            'promedio_mensual' => (($ingresos + $facturas + $lavados) - $egresos) / 12
        ];
    }

    public function getFlujoCaja(string $fechaInicio, string $fechaFin): array
    {
        // Obtener flujos por día
        $flujos = DB::select("
            SELECT 
                fecha,
                COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) as ingresos,
                COALESCE(SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END), 0) as egresos,
                COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END), 0) as flujo_neto
            FROM (
                SELECT fecha, monto, 'ingreso' as tipo FROM ingresos WHERE fecha BETWEEN ? AND ?
                UNION ALL
                SELECT fecha, monto, 'egreso' as tipo FROM egresos WHERE fecha BETWEEN ? AND ?
                UNION ALL
                SELECT DATE(created_at) as fecha, total as monto, 'ingreso' as tipo FROM facturas_electronicas WHERE DATE(created_at) BETWEEN ? AND ?
            ) as flujos
            GROUP BY fecha
            ORDER BY fecha
        ", [$fechaInicio, $fechaFin, $fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);

        return array_map(function($flujo) {
            return [
                'fecha' => $flujo->fecha,
                'ingresos' => $flujo->ingresos,
                'egresos' => $flujo->egresos,
                'flujo_neto' => $flujo->flujo_neto
            ];
        }, $flujos);
    }

    public function getIndicadoresFinancieros(string $fechaInicio, string $fechaFin): array
    {
        $balance = $this->getBalanceGeneral($fechaInicio, $fechaFin);
        $totalIngresos = $balance['ingresos'] + $balance['facturas'] + $balance['lavados'];
        $totalEgresos = $balance['egresos'];

        $margenBruto = $totalIngresos > 0 ? (($totalIngresos - $totalEgresos) / $totalIngresos) * 100 : 0;
        $rotacionEfectivo = $totalEgresos > 0 ? $totalIngresos / $totalEgresos : 0;

        return [
            'margen_bruto' => round($margenBruto, 2),
            'rotacion_efectivo' => round($rotacionEfectivo, 2),
            'rentabilidad' => round($balance['balance_neto'], 2),
            'total_ventas' => $balance['facturas'] + $balance['lavados'],
            'crecimiento_periodo' => $this->calcularCrecimiento($fechaInicio, $fechaFin)
        ];
    }

    public function getComparativoMensual(int $año): array
    {
        $añoAnterior = $año - 1;
        $mesesActual = $this->getBalancePorMes($año);
        $mesesAnterior = $this->getBalancePorMes($añoAnterior);

        $comparativo = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $actual = $mesesActual[$mes]['balance'];
            $anterior = $mesesAnterior[$mes]['balance'];
            
            $crecimiento = $anterior != 0 ? (($actual - $anterior) / $anterior) * 100 : 0;

            $comparativo[$mes] = [
                'mes' => $mesesActual[$mes]['mes'],
                'balance_actual' => $actual,
                'balance_anterior' => $anterior,
                'crecimiento' => round($crecimiento, 2)
            ];
        }

        return $comparativo;
    }

    public function getBalanceProjection(string $fechaInicio, string $fechaFin, int $diasProyeccion): array
    {
        $balance = $this->getBalanceGeneral($fechaInicio, $fechaFin);
        $diasPeriodo = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;
        
        $promedioIngresosDiario = ($balance['ingresos'] + $balance['facturas'] + $balance['lavados']) / $diasPeriodo;
        $promedioEgresosDiario = $balance['egresos'] / $diasPeriodo;

        $proyeccionIngresos = $promedioIngresosDiario * $diasProyeccion;
        $proyeccionEgresos = $promedioEgresosDiario * $diasProyeccion;

        return [
            'proyeccion_ingresos' => round($proyeccionIngresos, 2),
            'proyeccion_egresos' => round($proyeccionEgresos, 2),
            'proyeccion_balance' => round($proyeccionIngresos - $proyeccionEgresos, 2),
            'dias_proyeccion' => $diasProyeccion,
            'fecha_proyeccion' => Carbon::parse($fechaFin)->addDays($diasProyeccion)->format('Y-m-d')
        ];
    }

    private function calcularCrecimiento(string $fechaInicio, string $fechaFin): float
    {
        $periodoAnterior = Carbon::parse($fechaInicio)->subDays(
            Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1
        );

        $balanceActual = $this->getBalanceGeneral($fechaInicio, $fechaFin);
        $balanceAnterior = $this->getBalanceGeneral(
            $periodoAnterior->format('Y-m-d'),
            Carbon::parse($fechaInicio)->subDay()->format('Y-m-d')
        );

        $totalActual = $balanceActual['balance_neto'];
        $totalAnterior = $balanceAnterior['balance_neto'];

        return $totalAnterior != 0 ? (($totalActual - $totalAnterior) / $totalAnterior) * 100 : 0;
    }
}
