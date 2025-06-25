<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Lavado;
use App\Models\Ingreso;
use App\Models\Egreso;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TestController extends Controller
{
    public function testData()
    {
        try {
            $today = Carbon::today();
            
            $data = [
                'clientes' => Cliente::count(),
                'empleados' => Empleado::count(),
                'lavados_hoy' => Lavado::whereDate('created_at', $today)->count(),
                'ingresos_hoy' => Ingreso::whereDate('created_at', $today)->sum('monto'),
                'egresos_hoy' => Egreso::whereDate('created_at', $today)->sum('monto'),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
