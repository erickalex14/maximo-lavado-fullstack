<?php
// Controlador para la gestión de empleados
// Incluye CRUD y filtros de lavados por empleado, día, semana y mes

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Lavado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmpleadoController extends Controller
{
    // Mostrar la vista de empleados
    public function indexView()
    {
        return view('empleados.index');
    }

    // Listar todos los empleados
    public function index()
    {
        $empleados = Empleado::with('lavados')->get();
        if ($empleados->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay empleados registrados'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Empleados encontrados',
            'empleados' => $empleados
        ]);
    }

    // Crear un nuevo empleado
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'telefono' => 'required|string',
            'cedula' => 'required|string|unique:empleados,cedula',
            'tipo_salario' => 'required|in:mensual,diario,quincenal,semanal',
            'salario' => 'required|numeric',
        ]);
        $empleado = Empleado::create($validated);
        return response()->json([
            'message' => 'Empleado creado correctamente',
            'empleado' => $empleado
        ], 201);
    }

    // Mostrar un empleado específico
    public function show($id)
    {
        try {
            $empleado = Empleado::findOrFail($id);
            return response()->json([

                'empleado' => $empleado
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }
    }

    // Actualizar un empleado
    public function update(Request $request, $id)
    {
        try {
            $empleado = Empleado::findOrFail($id);
            $validated = $request->validate([
                'nombres' => 'sometimes|string',
                'apellidos' => 'sometimes|string',
                'telefono' => 'sometimes|string',
                'cedula' => 'sometimes|string|unique:empleados,cedula,' . $id . ',empleado_id',
                'tipo_salario' => 'sometimes|in:mensual,diario,quincenal,semanal',
                'salario' => 'sometimes|numeric',
            ]);
            $empleado->update($validated);
            return response()->json([
                'message' => 'Empleado actualizado correctamente',
                'empleado' => $empleado
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }
    }

    // Eliminar un empleado
    public function destroy($id)
    {
        try {
            $empleado = Empleado::findOrFail($id);
            $empleado->delete();
            return response()->json(['message' => 'Empleado eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }
    }

    // Filtro: Cantidad de lavados por empleado en un día
    public function lavadosPorDia($empleado_id, $fecha)
    {
        $count = Lavado::where('empleado_id', $empleado_id)
            ->whereDate('fecha', $fecha)
            ->count();
        return response()->json(['lavados' => $count]);
    }

    // Filtro: Cantidad de lavados por empleado en una semana (YYYY-MM-DD de cualquier día de la semana)
    public function lavadosPorSemana($empleado_id, $fecha)
    {
        $carbon = Carbon::parse($fecha);
        $start = $carbon->startOfWeek();
        $end = $carbon->endOfWeek();
        $count = Lavado::where('empleado_id', $empleado_id)
            ->whereBetween('fecha', [$start, $end])
            ->count();
        return response()->json(['lavados' => $count]);
    }

    // Filtro: Cantidad de lavados por empleado en un mes (YYYY-MM)
    public function lavadosPorMes($empleado_id, $anio, $mes)
    {
        $count = Lavado::where('empleado_id', $empleado_id)
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->count();
        return response()->json(['lavados' => $count]);
    }
}
