<?php
// Controlador para la gestión de clientes
// Incluye CRUD, búsqueda por cédula y buenas prácticas de manejo de errores

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClienteController extends Controller
{
    // Mostrar la vista de clientes
    public function indexView()
    {
        return view('clientes.index');
    }

    // Listar todos los clientes
    public function index()
    {
        $clientes = Cliente::all();
        if ($clientes->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay clientes registrados'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Clientes encontrados',
            'clientes' => $clientes
        ]);
    }

    // Crear un nuevo cliente
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'direccion' => 'nullable|string',
            'cedula' => 'nullable|string|unique:clientes,cedula',
        ]);
        $cliente = Cliente::create($validated);
        return response()->json([
            'message' => 'Cliente creado correctamente',
            'cliente' => $cliente
        ], 201);
    }

    // Mostrar un cliente específico
    public function show($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            return response()->json([
                'cliente' => $cliente
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }
    }

    // Actualizar un cliente
    public function update(Request $request, $id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $validated = $request->validate([
                'nombre' => 'sometimes|string',
                'telefono' => 'sometimes|string',
                'email' => 'sometimes|email',
                'direccion' => 'sometimes|string',
                'cedula' => 'sometimes|string|unique:clientes,cedula,' . $id . ',cliente_id',
            ]);
            $cliente->update($validated);
            return response()->json([
                'message' => 'Cliente actualizado correctamente',
                'cliente' => $cliente
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }
    }

    // Eliminar un cliente
    public function destroy($id)
    {
        try {
            $cliente = Cliente::findOrFail($id);
            $cliente->delete();
            return response()->json(['message' => 'Cliente eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }
    }

    // Buscar cliente por cédula
    public function buscarPorCedula($cedula)
    {
        $cliente = Cliente::where('cedula', $cedula)->first();
        if (!$cliente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente no encontrado con esa cédula'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'cliente' => $cliente
        ]);
    }
}
