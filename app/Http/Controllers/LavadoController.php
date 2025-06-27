<?php

namespace App\Http\Controllers;

use App\Services\LavadoService;
use App\Http\Requests\Lavado\CreateLavadoRequest;
use App\Http\Requests\Lavado\UpdateLavadoRequest;
use Illuminate\Http\JsonResponse;

class LavadoController extends Controller
{
    protected $lavadoService;

    public function __construct(LavadoService $lavadoService)
    {
        $this->lavadoService = $lavadoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $lavados = $this->lavadoService->getAllLavados();
            
            if ($lavados->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay lavados registrados'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lavados encontrados',
                'data' => $lavados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los lavados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLavadoRequest $request): JsonResponse
    {
        try {
            $lavado = $this->lavadoService->createLavado($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Lavado creado correctamente',
                'data' => $lavado
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el lavado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lavado = $this->lavadoService->findLavadoById($id);
            
            if (!$lavado) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lavado no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $lavado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el lavado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLavadoRequest $request, int $id): JsonResponse
    {
        try {
            $lavado = $this->lavadoService->updateLavado($id, $request->validated());
            
            if (!$lavado) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lavado no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lavado actualizado correctamente',
                'data' => $lavado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el lavado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->lavadoService->deleteLavado($id);
            
            if (!$deleted) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lavado no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lavado eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el lavado: ' . $e->getMessage()
            ], 500);
        }
    }
}
