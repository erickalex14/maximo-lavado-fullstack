<?php
// Controlador para la gestión de vehículos
// Incluye CRUD, validación de matrícula y buenas prácticas de manejo de errores

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VehiculoController extends Controller
{
    // Mostrar la vista de vehículos
    public function indexView()
    {
        return view('vehiculos.index');
    }

    // Listar todos los vehículos
    public function index()
    {
        $vehiculos = Vehiculo::with('cliente')->get();
        if ($vehiculos->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay vehículos registrados'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Vehículos encontrados',
            'vehiculos' => $vehiculos
        ]);
    }

    // Crear un nuevo vehículo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,cliente_id',
            'tipo' => 'required|in:moto,camioneta,auto_pequeno,auto_mediano',
            'matricula' => 'nullable|string|unique:vehiculos,matricula',
            'descripcion' => 'nullable|string',
        ]);
        // Validar matrícula obligatoria excepto para motos
        if ($validated['tipo'] !== 'moto' && empty($validated['matricula'])) {
            return response()->json([
                'message' => 'La matrícula es obligatoria para vehículos que no son motos.'
            ], 422);
        }
        $vehiculo = Vehiculo::create($validated);
        return response()->json([
            'message' => 'Vehículo creado correctamente',
            'vehiculo' => $vehiculo
        ], 201);
    }

    // Mostrar un vehículo específico
    public function show($id)
    {
        try {
            $vehiculo = Vehiculo::with('cliente')->findOrFail($id);
            return response()->json([
                'vehiculo' => $vehiculo
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }
    }

    // Actualizar un vehículo
    public function update(Request $request, $id)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $validated = $request->validate([
                'cliente_id' => 'sometimes|exists:clientes,cliente_id',
                'tipo' => 'sometimes|in:moto,camioneta,auto_pequeno,auto_mediano',
                'matricula' => 'nullable|string|unique:vehiculos,matricula,' . $id . ',vehiculo_id',
                'descripcion' => 'nullable|string',
            ]);
            // Validar matrícula obligatoria excepto para motos
            if (isset($validated['tipo']) && $validated['tipo'] !== 'moto' && empty($validated['matricula']) && empty($vehiculo->matricula)) {
                return response()->json([
                    'message' => 'La matrícula es obligatoria para vehículos que no son motos.'
                ], 422);
            }
            $vehiculo->update($validated);
            return response()->json([
                'message' => 'Vehículo actualizado correctamente',
                'vehiculo' => $vehiculo
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }
    }

    // Eliminar un vehículo
    public function destroy($id)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $vehiculo->delete();
            return response()->json(['message' => 'Vehículo eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }
    }

    // Obtener vehículos por cliente
    public function getByCliente($cliente_id)
    {
        $vehiculos = Vehiculo::where('cliente_id', $cliente_id)->get();
        return response()->json([
            'status' => 'success',
            'vehiculos' => $vehiculos
        ]);
    }
}
