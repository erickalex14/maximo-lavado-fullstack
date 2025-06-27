<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use App\Http\Requests\Reporte\ReporteFechasRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    protected $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    /**
     * Get available reports list.
     */
    public function index(): JsonResponse
    {
        try {
            $reportes = $this->reporteService->getReportesDisponibles();
            
            return response()->json([
                'status' => 'success',
                'data' => $reportes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener lista de reportes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ventas report.
     */
    public function reporteVentas(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteVentas(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lavados report.
     */
    public function reporteLavados(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteLavados(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de lavados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ingresos report.
     */
    public function reporteIngresos(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteIngresos(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de ingresos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get egresos report.
     */
    public function reporteEgresos(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteEgresos(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de egresos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get facturas report.
     */
    public function reporteFacturas(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteFacturas(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de facturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get empleados report.
     */
    public function reporteEmpleados(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteEmpleados(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de empleados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get productos report.
     */
    public function reporteProductos(Request $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteProductos(
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clientes report.
     */
    public function reporteClientes(Request $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteClientes(
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial report.
     */
    public function reporteFinanciero(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteFinanciero(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte financiero: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get balance report.
     */
    public function reporteBalance(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteBalance(
                $request->fecha_inicio,
                $request->fecha_fin,
                $request->formato ?? 'json'
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte de balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get complete business report.
     */
    public function reporteCompleto(ReporteFechasRequest $request): JsonResponse
    {
        try {
            $reporte = $this->reporteService->getReporteCompleto(
                $request->fecha_inicio,
                $request->fecha_fin
            );
            
            return response()->json([
                'status' => 'success',
                'data' => $reporte
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al generar reporte completo: ' . $e->getMessage()
            ], 500);
        }
    }
}
