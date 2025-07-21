<?php

namespace App\Http\Controllers;

use App\Services\FacturaElectronicaService;
use App\Http\Requests\FacturaElectronica\CreateFacturaElectronicaRequest;
use App\Http\Requests\FacturaElectronica\UpdateFacturaElectronicaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 🧾 FacturaElectronicaController V2 - Sistema de Facturación Electrónica SRI
 * 
 * Maneja todo el ciclo de facturación electrónica:
 * - Generación automática desde ventas
 * - Envío al SRI para autorización
 * - Gestión de estados (GENERADA, AUTORIZADA, ANULADA)
 * - Reenvío de facturas rechazadas
 * - Reportes y consultas
 * 
 * INTEGRACIÓN CON SRI:
 * - Cumple normativas ecuatorianas
 * - XML firmado digitalmente
 * - Clave de acceso automática
 * - Respuestas en tiempo real
 */
class FacturaElectronicaController extends Controller
{
    protected FacturaElectronicaService $facturaElectronicaService;

    public function __construct(FacturaElectronicaService $facturaElectronicaService)
    {
        $this->facturaElectronicaService = $facturaElectronicaService;
    }

    /**
     * Obtener todas las facturas electrónicas con filtros
     * GET /facturas-electronicas
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'estado_sri', 'fecha_inicio', 'fecha_fin', 
                'cliente_id', 'numero_autorizacion'
            ]);

            $facturas = $this->facturaElectronicaService->getAll($filters);

            return $this->successResponse($facturas, 'facturas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener las facturas electrónicas', $e);
        }
    }

    /**
     * Crear factura electrónica manual
     * POST /facturas-electronicas
     */
    public function store(CreateFacturaElectronicaRequest $request): JsonResponse
    {
        try {
            $factura = $this->facturaElectronicaService->crearFactura($request->validated());
            
            return $this->successResponse($factura, 'factura', 'Factura electrónica creada correctamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear la factura electrónica', $e);
        }
    }

    /**
     * Obtener factura electrónica específica
     * GET /facturas-electronicas/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $factura = $this->facturaElectronicaService->getById($id);
            
            if (!$factura) {
                return $this->notFoundResponse('Factura electrónica no encontrada');
            }

            return $this->successResponse($factura, 'factura');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener la factura electrónica', $e);
        }
    }

    /**
     * Actualizar factura electrónica
     * PUT /facturas-electronicas/{id}
     */
    public function update(int $id, UpdateFacturaElectronicaRequest $request): JsonResponse
    {
        try {
            $factura = $this->facturaElectronicaService->update($id, $request->validated());
            
            if (!$factura) {
                return $this->notFoundResponse('Factura electrónica no encontrada');
            }

            return $this->successResponse($factura, 'factura', 'Factura electrónica actualizada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar la factura electrónica', $e);
        }
    }

    /**
     * Procesar factura con SRI
     * POST /facturas-electronicas/{id}/procesar-sri
     */
    public function procesarConSRI(int $id): JsonResponse
    {
        try {
            $resultado = $this->facturaElectronicaService->procesarConSRI($id);
            
            return $this->successResponse($resultado, 'resultado', 'Factura procesada con SRI');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al procesar factura con SRI', $e);
        }
    }

    /**
     * Reenviar factura rechazada al SRI
     * POST /facturas-electronicas/{id}/reenviar
     */
    public function reenviarAlSRI(int $id): JsonResponse
    {
        try {
            $resultado = $this->facturaElectronicaService->reenviarAlSRI($id);
            
            return $this->successResponse($resultado, 'resultado', 'Factura reenviada al SRI');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al reenviar factura al SRI', $e);
        }
    }

    /**
     * Anular factura electrónica
     * POST /facturas-electronicas/{id}/anular
     */
    public function anular(int $id, Request $request): JsonResponse
    {
        try {
            $motivo = $request->input('motivo', 'Anulación solicitada por usuario');
            
            $factura = $this->facturaElectronicaService->anularFactura($id, $motivo);
            
            return $this->successResponse($factura, 'factura', 'Factura anulada correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al anular la factura', $e);
        }
    }

    /**
     * Obtener XML de factura
     * GET /facturas-electronicas/{id}/xml
     */
    public function getXML(int $id): JsonResponse
    {
        try {
            $xml = $this->facturaElectronicaService->getXML($id);
            
            if (!$xml) {
                return $this->notFoundResponse('XML de factura no encontrado');
            }

            return response()->json([
                'success' => true,
                'xml' => $xml
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener XML de la factura', $e);
        }
    }

    /**
     * Descargar PDF de factura
     * GET /facturas-electronicas/{id}/pdf
     */
    public function downloadPDF(int $id)
    {
        try {
            return $this->facturaElectronicaService->generatePDF($id);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar PDF de la factura', $e);
        }
    }

    /**
     * Obtener facturas por venta
     * GET /facturas-electronicas/venta/{ventaId}
     */
    public function getByVenta(int $ventaId): JsonResponse
    {
        try {
            $factura = $this->facturaElectronicaService->getByVentaId($ventaId);
            
            if (!$factura) {
                return $this->notFoundResponse('No se encontró factura para esta venta');
            }

            return $this->successResponse($factura, 'factura');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener factura por venta', $e);
        }
    }

    /**
     * Obtener estadísticas de facturación electrónica
     * GET /facturas-electronicas/estadisticas
     */
    public function getEstadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->facturaElectronicaService->getEstadisticas();
            
            return $this->successResponse($estadisticas, 'estadisticas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas', $e);
        }
    }

    /**
     * Obtener facturas pendientes de envío al SRI
     * GET /facturas-electronicas/pendientes-sri
     */
    public function getPendientesSRI(): JsonResponse
    {
        try {
            $facturas = $this->facturaElectronicaService->getPendientesEnvioSRI();
            
            return $this->successResponse($facturas, 'facturas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener facturas pendientes', $e);
        }
    }

    /**
     * Procesar lote de facturas pendientes
     * POST /facturas-electronicas/procesar-lote
     */
    public function procesarLote(Request $request): JsonResponse
    {
        try {
            $limite = $request->input('limite', 10);
            
            $resultado = $this->facturaElectronicaService->procesarLotePendientes($limite);
            
            return $this->successResponse($resultado, 'resultado', 'Lote procesado correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al procesar lote de facturas', $e);
        }
    }

    /**
     * Validar conexión con SRI
     * GET /facturas-electronicas/validar-sri
     */
    public function validarConexionSRI(): JsonResponse
    {
        try {
            $resultado = $this->facturaElectronicaService->validarConexionSRI();
            
            return $this->successResponse($resultado, 'conexion');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al validar conexión con SRI', $e);
        }
    }
}
