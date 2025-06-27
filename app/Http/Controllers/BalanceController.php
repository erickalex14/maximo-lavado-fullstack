<?php

namespace App\Http\Controllers;

use App\Services\BalanceService;
use App\Http\Requests\Balance\BalanceRequest;
use Illuminate\Http\JsonResponse;

class BalanceController extends Controller
{
    protected $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    public function balanceGeneral(BalanceRequest $request): JsonResponse
    {
        try {
            $balance = $this->balanceService->getBalanceGeneral($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $balance,
                'message' => 'Balance general obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el balance general: ' . $e->getMessage()
            ], 500);
        }
    }

    public function balancePorCategoria(BalanceRequest $request): JsonResponse
    {
        try {
            $balance = $this->balanceService->getBalancePorCategoria($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $balance,
                'message' => 'Balance por categoría obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el balance por categoría: ' . $e->getMessage()
            ], 500);
        }
    }

    public function balancePorMes(BalanceRequest $request): JsonResponse
    {
        try {
            $año = $request->validated()['año'] ?? now()->year;
            $balance = $this->balanceService->getBalancePorMes($año);
            
            return response()->json([
                'success' => true,
                'data' => $balance,
                'message' => 'Balance mensual obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el balance mensual: ' . $e->getMessage()
            ], 500);
        }
    }

    public function balancePorTrimestre(BalanceRequest $request): JsonResponse
    {
        try {
            $año = $request->validated()['año'] ?? now()->year;
            $balance = $this->balanceService->getBalancePorTrimestre($año);
            
            return response()->json([
                'success' => true,
                'data' => $balance,
                'message' => 'Balance trimestral obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el balance trimestral: ' . $e->getMessage()
            ], 500);
        }
    }

    public function balanceAnual(BalanceRequest $request): JsonResponse
    {
        try {
            $año = $request->validated()['año'] ?? now()->year;
            $balance = $this->balanceService->getBalanceAnual($año);
            
            return response()->json([
                'success' => true,
                'data' => $balance,
                'message' => 'Balance anual obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el balance anual: ' . $e->getMessage()
            ], 500);
        }
    }

    public function flujoCaja(BalanceRequest $request): JsonResponse
    {
        try {
            $flujo = $this->balanceService->getFlujoCaja($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $flujo,
                'message' => 'Flujo de caja obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el flujo de caja: ' . $e->getMessage()
            ], 500);
        }
    }

    public function indicadoresFinancieros(BalanceRequest $request): JsonResponse
    {
        try {
            $indicadores = $this->balanceService->getIndicadoresFinancieros($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $indicadores,
                'message' => 'Indicadores financieros obtenidos exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los indicadores financieros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function comparativoMensual(BalanceRequest $request): JsonResponse
    {
        try {
            $año = $request->validated()['año'] ?? now()->year;
            $comparativo = $this->balanceService->getComparativoMensual($año);
            
            return response()->json([
                'success' => true,
                'data' => $comparativo,
                'message' => 'Comparativo mensual obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el comparativo mensual: ' . $e->getMessage()
            ], 500);
        }
    }

    public function proyeccion(BalanceRequest $request): JsonResponse
    {
        try {
            $proyeccion = $this->balanceService->getBalanceProjection($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $proyeccion,
                'message' => 'Proyección de balance obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la proyección de balance: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resumenCompleto(BalanceRequest $request): JsonResponse
    {
        try {
            $resumen = $this->balanceService->getResumenCompleto($request->validated());
            
            return response()->json([
                'success' => true,
                'data' => $resumen,
                'message' => 'Resumen completo de balance obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen completo: ' . $e->getMessage()
            ], 500);
        }
    }
}
