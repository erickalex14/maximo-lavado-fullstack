<?php
// Controlador para la gestión de lavados
// Incluye CRUD, lógica de precios, pulverizado y buenas prácticas de manejo de errores

namespace App\Http\Controllers;

use App\Models\Lavado;
use App\Models\Vehiculo;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;

class LavadoController extends Controller
{
    // Mostrar la vista de lavados
    public function indexView()
    {
        return view('lavados.index');
    }

    // Listar todos los lavados
    public function index()
    {
        $lavados = Lavado::with(['vehiculo', 'empleado'])->get();
        if ($lavados->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay lavados registrados'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Lavados encontrados',
            'lavados' => $lavados
        ]);
    }

    // Crear un nuevo lavado
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehiculo_id' => 'required|exists:vehiculos,vehiculo_id',
            'empleado_id' => 'required|exists:empleados,empleado_id',
            'fecha' => 'required|date',
            'tipo_lavado' => 'required|in:completo,solo_fuera,solo_por_dentro',
            'precio' => 'nullable|numeric',
            'pulverizado' => 'boolean',
        ]);

        // Lógica de precio predeterminado según tipo de vehículo y tipo de lavado
        $vehiculo = Vehiculo::find($validated['vehiculo_id']);
        $precio = $validated['precio'] ?? $this->calcularPrecio($vehiculo->tipo, $validated['tipo_lavado']);
        if (!empty($validated['pulverizado'])) {
            $precio += 2;
        }
        $lavado = Lavado::create([
            'vehiculo_id' => $validated['vehiculo_id'],
            'empleado_id' => $validated['empleado_id'],
            'fecha' => $validated['fecha'],
            'tipo_lavado' => $validated['tipo_lavado'],
            'precio' => $precio,
            'pulverizado' => $validated['pulverizado'] ?? false,
        ]);
        return response()->json([
            'message' => 'Lavado registrado correctamente',
            'lavado' => $lavado
        ], 201);
    }

    // Mostrar un lavado específico
    public function show($id)
    {
        try {
            $lavado = Lavado::with(['vehiculo', 'empleado'])->findOrFail($id);
            return response()->json([
                'lavado' => $lavado
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lavado no encontrado'], 404);
        }
    }

    // Actualizar un lavado
    public function update(Request $request, $id)
    {
        try {
            $lavado = Lavado::findOrFail($id);
            $validated = $request->validate([
                'vehiculo_id' => 'sometimes|exists:vehiculos,vehiculo_id',
                'empleado_id' => 'sometimes|exists:empleados,empleado_id',
                'fecha' => 'sometimes|date',
                'tipo_lavado' => 'sometimes|in:completo,solo_fuera,solo_por_dentro',
                'precio' => 'nullable|numeric',
                'pulverizado' => 'boolean',
            ]);
            // Si no se envía precio, recalcular
            if (!isset($validated['precio'])) {
                $vehiculo = Vehiculo::find($validated['vehiculo_id'] ?? $lavado->vehiculo_id);
                $tipo_lavado = $validated['tipo_lavado'] ?? $lavado->tipo_lavado;
                $precio = $this->calcularPrecio($vehiculo->tipo, $tipo_lavado);
                if (isset($validated['pulverizado']) ? $validated['pulverizado'] : $lavado->pulverizado) {
                    $precio += 2;
                }
                $validated['precio'] = $precio;
            }
            $lavado->update($validated);
            return response()->json([
                'message' => 'Lavado actualizado correctamente',
                'lavado' => $lavado
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lavado no encontrado'], 404);
        }
    }

    // Eliminar un lavado
    public function destroy($id)
    {
        try {
            $lavado = Lavado::findOrFail($id);
            $lavado->delete();
            return response()->json(['message' => 'Lavado eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lavado no encontrado'], 404);
        }
    }

    // Lógica de precios predeterminados
    private function calcularPrecio($tipoVehiculo, $tipoLavado)
    {
        $precios = [
            'moto' => [
                'completo' => 3,
                'solo_fuera' => 3,
                'solo_por_dentro' => 3,
            ],
            'auto_pequeno' => [
                'completo' => 8,
                'solo_fuera' => 4.5,
                'solo_por_dentro' => 4,
            ],
            'auto_mediano' => [
                'completo' => 10,
                'solo_fuera' => 5,
                'solo_por_dentro' => 5,
            ],
            'camioneta' => [
                'completo' => 10,
                'solo_fuera' => 8,
                'solo_por_dentro' => 7,
            ],
        ];
        return $precios[$tipoVehiculo][$tipoLavado] ?? 0;
    }
}
