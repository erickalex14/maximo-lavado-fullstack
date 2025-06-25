<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\PagoProveedor;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    // Mostrar la vista de proveedores
    public function indexView()
    {
        return view('proveedores.index');
    }

    // Listar todos los proveedores
    public function index()
    {
        $proveedores = Proveedor::all();
        return response()->json($proveedores);
    }

    // Crear un nuevo proveedor
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);
        $proveedor = Proveedor::create($validated);
        return response()->json(['message' => 'Proveedor creado correctamente', 'proveedor' => $proveedor], 201);
    }

    // Mostrar un proveedor específico
    public function show($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            return response()->json($proveedor);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }
    }

    // Actualizar un proveedor
    public function update(Request $request, $id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:255',
                'telefono' => 'nullable|string|max:30',
                'direccion' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
            ]);
            $proveedor->update($validated);
            return response()->json(['message' => 'Proveedor actualizado correctamente', 'proveedor' => $proveedor]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }
    }

    // Eliminar un proveedor
    public function destroy($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            $proveedor->delete();
            return response()->json(['message' => 'Proveedor eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }
    }

    // Ver deuda actual del proveedor
    public function verDeuda($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            return response()->json([
                'proveedor_id' => $proveedor->getKey(),
                'deuda_pendiente' => $proveedor->deuda_pendiente
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }
    }

    // Registrar un pago a proveedor
    public function registrarPago(Request $request, $id)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:255',
        ]);
        try {
            $proveedor = Proveedor::findOrFail($id);
            DB::beginTransaction();
            $nuevoSaldo = $proveedor->deuda_pendiente - $validated['monto'];
            if ($nuevoSaldo < 0) {
                DB::rollBack();
                return response()->json(['message' => 'El monto excede la deuda pendiente'], 400);
            }
            $proveedor->deuda_pendiente = $nuevoSaldo;
            $proveedor->save();
            // Registrar el pago en la tabla pagos_proveedores
            PagoProveedor::create([
                'proveedor_id' => $proveedor->getKey(),
                'monto' => $validated['monto'],
                'fecha' => now(),
                'descripcion' => $validated['descripcion'] ?? null,
            ]);
            DB::commit();
            return response()->json([
                'message' => 'Pago registrado correctamente',
                'deuda_pendiente' => $proveedor->deuda_pendiente
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar el pago', 'error' => $e->getMessage()], 500);
        }
    }

    // Historial de pagos a proveedor
    public function pagos($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            $pagos = $proveedor->pagos()->orderByDesc('fecha')->get();
            return response()->json($pagos);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }
    }
}
