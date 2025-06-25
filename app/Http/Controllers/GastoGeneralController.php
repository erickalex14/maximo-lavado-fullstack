<?php

namespace App\Http\Controllers;

use App\Models\GastoGeneral;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GastoGeneralController extends Controller
{
    public function index(Request $request)
    {
        $query = GastoGeneral::query();
        // Filtros por fecha y tipo
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        $gastos = $query->get();
        return response()->json($gastos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'tipo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $gasto = GastoGeneral::create($validated);
        return response()->json(['message' => 'Gasto general registrado correctamente', 'gasto' => $gasto], 201);
    }

    public function show($id)
    {
        try {
            $gasto = GastoGeneral::findOrFail($id);
            return response()->json($gasto);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Gasto general no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $gasto = GastoGeneral::findOrFail($id);
            $validated = $request->validate([
                'monto' => 'sometimes|required|numeric|min:0.01',
                'fecha' => 'sometimes|required|date',
                'tipo' => 'sometimes|required|string|max:100',
                'descripcion' => 'nullable|string|max:255',
            ]);
            $gasto->update($validated);
            return response()->json(['message' => 'Gasto general actualizado correctamente', 'gasto' => $gasto]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Gasto general no encontrado'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $gasto = GastoGeneral::findOrFail($id);
            $gasto->delete();
            return response()->json(['message' => 'Gasto general eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Gasto general no encontrado'], 404);
        }
    }
}
