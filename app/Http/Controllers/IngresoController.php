<?php

namespace App\Http\Controllers;

use App\Models\Ingreso;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IngresoController extends Controller
{
    public function index(Request $request)
    {
        $query = Ingreso::query();
        // Filtros por fecha y tipo
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);
        }
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        $ingresos = $query->get();
        return response()->json($ingresos);
    }    public function store(Request $request)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'tipo' => 'required|in:lavado,producto_automotriz,producto_despensa',
            'referencia_id' => 'nullable|integer',
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Validar que referencia_id corresponda al tipo seleccionado
        if ($validated['referencia_id']) {
            $this->validarReferencia($validated['tipo'], $validated['referencia_id']);
        }

        $ingreso = Ingreso::create($validated);
        return response()->json(['message' => 'Ingreso registrado correctamente', 'ingreso' => $ingreso], 201);
    }

    private function validarReferencia($tipo, $referenciaId)
    {
        switch ($tipo) {
            case 'lavado':
                if (!\App\Models\Lavado::where('lavado_id', $referenciaId)->exists()) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['referencia_id' => 'El lavado especificado no existe']
                    );
                }
                break;
            case 'producto_automotriz':
                if (!\App\Models\VentaProductoAutomotriz::where('id', $referenciaId)->exists()) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['referencia_id' => 'La venta de producto automotriz especificada no existe']
                    );
                }
                break;
            case 'producto_despensa':
                if (!\App\Models\VentaProductoDespensa::where('id', $referenciaId)->exists()) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        ['referencia_id' => 'La venta de producto despensa especificada no existe']
                    );
                }
                break;
        }
    }

    public function show($id)
    {
        try {
            $ingreso = Ingreso::findOrFail($id);
            return response()->json($ingreso);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ingreso no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $ingreso = Ingreso::findOrFail($id);
            $validated = $request->validate([
                'monto' => 'sometimes|required|numeric|min:0.01',
                'fecha' => 'sometimes|required|date',
                'tipo' => 'sometimes|required|in:lavado,producto_automotriz,producto_despensa',
                'referencia_id' => 'nullable|integer',
                'descripcion' => 'nullable|string|max:255',
            ]);

            // Validar que referencia_id corresponda al tipo si ambos están presentes
            if (isset($validated['tipo']) && isset($validated['referencia_id'])) {
                $this->validarReferencia($validated['tipo'], $validated['referencia_id']);
            } elseif (isset($validated['referencia_id']) && !isset($validated['tipo'])) {
                $this->validarReferencia($ingreso->tipo, $validated['referencia_id']);
            }

            $ingreso->update($validated);
            return response()->json(['message' => 'Ingreso actualizado correctamente', 'ingreso' => $ingreso]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ingreso no encontrado'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $ingreso = Ingreso::findOrFail($id);
            $ingreso->delete();
            return response()->json(['message' => 'Ingreso eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Ingreso no encontrado'], 404);
        }
    }
}
