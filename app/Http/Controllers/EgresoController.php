<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EgresoController extends Controller
{
    public function index(Request $request)
    {
        $query = Egreso::query();
        // Filtros por fecha y tipo
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        $egresos = $query->get();
        return response()->json($egresos);
    }    public function store(Request $request)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'tipo' => 'required|in:pago_proveedor,gasto_general',
            'referencia_id' => 'nullable|integer',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Validar que referencia_id corresponda al tipo seleccionado
        if ($validated['referencia_id']) {
            $this->validarReferencia($validated['tipo'], $validated['referencia_id']);
        }

        $egreso = Egreso::create($validated);
        return response()->json(['message' => 'Egreso registrado correctamente', 'egreso' => $egreso], 201);
    }

    private function validarReferencia($tipo, $referenciaId)
    {
        switch ($tipo) {
            case 'pago_proveedor':
                if (!\App\Models\PagoProveedor::where('id_pago_proveedor', $referenciaId)->exists()) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['referencia_id' => 'El pago a proveedor especificado no existe']
                    );
                }
                break;
            case 'gasto_general':
                if (!\App\Models\GastoGeneral::where('gasto_general_id', $referenciaId)->exists()) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['referencia_id' => 'El gasto general especificado no existe']
                    );
                }
                break;
        }
    }

    public function show($id)
    {
        try {
            $egreso = Egreso::findOrFail($id);
            return response()->json($egreso);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Egreso no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $egreso = Egreso::findOrFail($id);
            $validated = $request->validate([
                'monto' => 'sometimes|required|numeric|min:0.01',
                'fecha' => 'sometimes|required|date',
                'tipo' => 'sometimes|required|in:pago_proveedor,gasto_general',
                'referencia_id' => 'nullable|integer',
                'descripcion' => 'nullable|string|max:255',
            ]);

            // Validar que referencia_id corresponda al tipo si ambos están presentes
            if (isset($validated['tipo']) && isset($validated['referencia_id'])) {
                $this->validarReferencia($validated['tipo'], $validated['referencia_id']);
            } elseif (isset($validated['referencia_id']) && !isset($validated['tipo'])) {
                $this->validarReferencia($egreso->tipo, $validated['referencia_id']);
            }

            $egreso->update($validated);
            return response()->json(['message' => 'Egreso actualizado correctamente', 'egreso' => $egreso]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Egreso no encontrado'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $egreso = Egreso::findOrFail($id);
            $egreso->delete();
            return response()->json(['message' => 'Egreso eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Egreso no encontrado'], 404);
        }
    }
}
