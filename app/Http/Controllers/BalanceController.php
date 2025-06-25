<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use App\Models\Egreso;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    // Resumen de balance general
    public function resumen(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $tipoIngreso = $request->input('tipo_ingreso');
        $tipoEgreso = $request->input('tipo_egreso');
        $metodoPago = $request->input('metodo_pago');
        $usuarioId = $request->input('usuario_id');

        $ingresosQuery = Ingreso::query();
        $egresosQuery = Egreso::query();

        if ($fechaInicio && $fechaFin) {
            $ingresosQuery->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            $egresosQuery->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        if ($tipoIngreso) {
            $ingresosQuery->where('tipo', $tipoIngreso);
        }
        if ($tipoEgreso) {
            $egresosQuery->where('tipo', $tipoEgreso);
        }
        if ($metodoPago) {
            $ingresosQuery->where('metodo_pago', $metodoPago);
            $egresosQuery->where('metodo_pago', $metodoPago);
        }
        if ($usuarioId) {
            $ingresosQuery->where('usuario_id', $usuarioId);
            $egresosQuery->where('usuario_id', $usuarioId);
        }

        $totalIngresos = $ingresosQuery->sum('monto');
        $totalEgresos = $egresosQuery->sum('monto');
        $saldo = $totalIngresos - $totalEgresos;

        return response()->json([
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'saldo' => $saldo,
            'periodo' => $fechaInicio && $fechaFin ? [$fechaInicio, $fechaFin] : 'completo',
            'filtros' => [
                'tipo_ingreso' => $tipoIngreso,
                'tipo_egreso' => $tipoEgreso,
                'metodo_pago' => $metodoPago,
                'usuario_id' => $usuarioId,
            ]
        ]);
    }
}
