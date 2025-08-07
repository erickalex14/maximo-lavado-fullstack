<?php

namespace App\Services;

use App\Contracts\FacturaElectronicaRepositoryInterface;
use App\Models\FacturaElectronica;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacturaElectronicaService
{
    protected FacturaElectronicaRepositoryInterface $facturaRepository;

    public function __construct(FacturaElectronicaRepositoryInterface $facturaRepository)
    {
        $this->facturaRepository = $facturaRepository;
    }

    /**
     * Obtener todas las facturas electrónicas
     */
    public function getAll(array $filters = []): Collection
    {
        // Por ahora retornamos todas las facturas, en el futuro se pueden aplicar filtros
        return $this->facturaRepository->getAll();
    }

    /**
     * Obtener factura por ID
     */
    public function getById(int $id): ?FacturaElectronica
    {
        return $this->facturaRepository->findById($id);
    }

    /**
     * Generar factura electrónica desde una venta
     */
    public function generarDesdeVenta(Venta $venta, array $datosEmisor = []): FacturaElectronica
    {
        try {
            return DB::transaction(function () use ($venta, $datosEmisor) {
                // Validar que la venta no tenga ya una factura
                if ($venta->facturaElectronica) {
                    throw new \Exception("La venta ya tiene una factura electrónica asociada");
                }

                // Obtener datos del cliente
                $cliente = $venta->cliente;
                if (!$cliente) {
                    throw new \Exception("La venta debe tener un cliente asociado");
                }

                // Generar secuencial único
                $secuencial = $this->facturaRepository->getNextSecuencial(
                    $datosEmisor['establecimiento'] ?? '001',
                    $datosEmisor['punto_emision'] ?? '001'
                );

                // Preparar datos de la factura
                $datosFactura = [
                    'venta_id' => $venta->venta_id,
                    
                    // Datos del emisor
                    'ruc_emisor' => $datosEmisor['ruc_emisor'] ?? config('sri.ruc'),
                    'razon_social_emisor' => $datosEmisor['razon_social_emisor'] ?? config('sri.razon_social'),
                    'direccion_emisor' => $datosEmisor['direccion_emisor'] ?? config('sri.direccion_matriz'),
                    'establecimiento' => $datosEmisor['establecimiento'] ?? config('sri.establecimiento', '001'),
                    'punto_emision' => $datosEmisor['punto_emision'] ?? config('sri.punto_emision', '001'),
                    
                    // Datos del comprador (snapshot para inmutabilidad SRI)
                    'identificacion_comprador' => $cliente->cedula ?? '9999999999999',
                    'razon_social_comprador' => $cliente->nombre,
                    'direccion_comprador' => $cliente->direccion,
                    'email_comprador' => $cliente->email,
                    
                    // Información del documento
                    'tipo_documento' => '01', // 01: Factura
                    'secuencial' => $secuencial,
                    'ambiente' => config('sri.ambiente', '1'), // 1: Pruebas, 2: Producción
                    'tipo_emision' => '1', // 1: Emisión normal
                    
                    // Valores monetarios (desde la venta)
                    'subtotal' => $venta->subtotal ?? 0,
                    'descuento' => $venta->descuento ?? 0,
                    'iva' => $venta->iva ?? 0,
                    'total' => $venta->total ?? 0,
                    
                    // ⚡ Estado inicial CORRECTO para procesamiento automático ⚡
                    'estado_sri' => 'BORRADOR', // CRUCIAL: Iniciar como BORRADOR (no 'GENERADA')
                ];

                // Crear la factura
                $factura = $this->facturaRepository->create($datosFactura);

                // Generar XML del documento
                $this->generarXMLDocumento($factura);

                // 🔄 RECARGAR FACTURA CON XML GENERADO
                $factura = $this->facturaRepository->findById($factura->factura_electronica_id);

                // ⚡ PROCESAMIENTO AUTOMÁTICO CON SRI
                try {
                    $facturaActualizada = $this->procesarAutomaticoConSRI($factura);
                    
                    Log::info('✅ Factura electrónica generada Y procesada automáticamente', [
                        'factura_id' => $facturaActualizada->factura_electronica_id,
                        'venta_id' => $venta->venta_id,
                        'secuencial' => $secuencial,
                        'estado_sri' => $facturaActualizada->estado_sri,
                        'total' => $venta->total
                    ]);
                    
                    return $facturaActualizada;
                } catch (\Exception $e) {
                    // Si falla el procesamiento automático, log warning pero no fallar la transacción
                    Log::warning('⚠️ Factura generada pero falló procesamiento automático con SRI', [
                        'factura_id' => $factura->factura_electronica_id,
                        'error' => $e->getMessage(),
                        'nota' => 'Se puede reenviar manualmente'
                    ]);
                    
                    return $factura;
                }
            });
        } catch (\Exception $e) {
            Log::error('Error al generar factura electrónica', [
                'venta_id' => $venta->venta_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Procesar factura con el SRI
     */
    public function procesarConSRI(int $facturaId): FacturaElectronica
    {
        $factura = $this->facturaRepository->findById($facturaId);
        
        if (!$factura) {
            throw new \Exception("Factura no encontrada con ID: {$facturaId}");
        }

        if ($factura->estado_sri !== 'BORRADOR') {
            throw new \Exception("Solo se pueden procesar facturas en estado BORRADOR");
        }

        try {
            return DB::transaction(function () use ($factura) {
                // Simular procesamiento con SRI
                // En producción aquí iría la integración real con el SRI
                $resultado = $this->simularProcesarSRI($factura);

                if ($resultado['autorizada']) {
                    $this->facturaRepository->updateEstadoSri(
                        $factura->factura_electronica_id,
                        'AUTORIZADA',
                        $resultado['mensaje'],
                        null
                    );

                    // Generar clave de acceso y XML autorizado
                    $claveAcceso = $this->generarClaveAcceso($factura);
                    $xmlAutorizado = $this->generarXMLAutorizado($factura, $claveAcceso);
                    
                    $this->facturaRepository->updateXmlAutorizado(
                        $factura->factura_electronica_id,
                        $xmlAutorizado,
                        $claveAcceso
                    );
                } else {
                    $this->facturaRepository->updateEstadoSri(
                        $factura->factura_electronica_id,
                        'RECHAZADA',
                        $resultado['mensaje'],
                        $resultado['errores']
                    );
                }

                Log::info('Factura procesada con SRI', [
                    'factura_id' => $factura->factura_electronica_id,
                    'estado' => $resultado['autorizada'] ? 'AUTORIZADA' : 'RECHAZADA'
                ]);

                return $this->facturaRepository->findById($factura->factura_electronica_id);
            });
        } catch (\Exception $e) {
            Log::error('Error al procesar factura con SRI', [
                'factura_id' => $facturaId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 🚀 Procesar automáticamente una factura con SRI (sin validaciones manuales)
     * Este método se ejecuta automáticamente después de generar la factura
     */
    public function procesarAutomaticoConSRI(FacturaElectronica $factura): FacturaElectronica
    {
        try {
            // Simular procesamiento con SRI
            Log::info('📤 Iniciando procesamiento automático con SRI', [
                'factura_id' => $factura->factura_electronica_id,
                'secuencial' => $factura->secuencial
            ]);
            
            // Simular tiempo de procesamiento SRI (0.5-2 segundos)
            usleep(rand(500000, 2000000));
            
            // Simular respuesta del SRI (95% éxito, 5% rechazo por validación automática)
            $exito = (rand(1, 100) <= 95);
            
            if ($exito) {
                $claveAcceso = $this->generarClaveAcceso($factura);
                $numeroAutorizacion = 'AUT-' . time() . rand(1000, 9999);
                
                // 🔥 GENERAR XML AUTORIZADO COMPLETO 🔥
                $xmlAutorizado = $this->generarXMLAutorizado($factura, $claveAcceso);
                
                $factura = $this->facturaRepository->update($factura->factura_electronica_id, [
                    'estado_sri' => 'AUTORIZADA',
                    'clave_acceso' => $claveAcceso,
                    'fecha_autorizacion' => now(),
                    'numero_autorizacion' => $numeroAutorizacion,
                    'xml_autorizado' => $xmlAutorizado,
                    'mensaje_sri' => 'AUTORIZADA - Comprobante procesado correctamente',
                    'errores_sri' => null // Limpiar errores previos
                ]);
                
                Log::info('✅ Factura procesada automáticamente - AUTORIZADA por SRI', [
                    'factura_id' => $factura->factura_electronica_id,
                    'clave_acceso' => $claveAcceso,
                    'numero_autorizacion' => $numeroAutorizacion,
                    'estado' => 'AUTORIZADA'
                ]);
            } else {
                $factura = $this->facturaRepository->update($factura->factura_electronica_id, [
                    'estado_sri' => 'RECHAZADA',
                    'mensaje_sri' => 'RECHAZADA - Error en datos tributarios',
                    'errores_sri' => [
                        [
                            'codigo' => 'SRI001', 
                            'mensaje' => 'Error simulado: Datos tributarios inconsistentes'
                        ]
                    ]
                ]);
                
                Log::warning('❌ Factura RECHAZADA automáticamente por SRI', [
                    'factura_id' => $factura->factura_electronica_id,
                    'motivo' => 'Error en datos tributarios - verificar información'
                ]);
            }
            
            return $factura;
            
        } catch (\Exception $e) {
            Log::error('💥 Error en procesamiento automático con SRI', [
                'factura_id' => $factura->factura_electronica_id,
                'error' => $e->getMessage()
            ]);
            throw new \Exception("Error en procesamiento automático SRI: " . $e->getMessage());
        }
    }

    /**
     * Obtener factura por venta ID
     */
    public function getPorVentaId(int $ventaId): ?FacturaElectronica
    {
        return $this->facturaRepository->findByVentaId($ventaId);
    }

    /**
     * Obtener facturas por rango de fechas
     */
    public function getPorRangoFechas(string $fechaInicio, string $fechaFin): Collection
    {
        return $this->facturaRepository->getByDateRange($fechaInicio, $fechaFin);
    }

    /**
     * Obtener facturas por estado SRI
     */
    public function getPorEstadoSRI(string $estado): Collection
    {
        return $this->facturaRepository->getByEstadoSri($estado);
    }

    /**
     * Generar PDF de la factura
     */
    public function generarPDF(int $facturaId): string
    {
        $factura = $this->facturaRepository->findById($facturaId);
        
        if (!$factura) {
            throw new \Exception("Factura no encontrada con ID: {$facturaId}");
        }

        try {
            // Aquí iría la lógica para generar el PDF
            // Por ahora retornamos una ruta simulada
            $rutaPDF = storage_path("app/facturas/factura_{$facturaId}.pdf");
            
            // Simular generación del PDF
            $this->generarArchivoPDF($factura, $rutaPDF);

            Log::info('PDF de factura generado', [
                'factura_id' => $facturaId,
                'ruta' => $rutaPDF
            ]);

            return $rutaPDF;
        } catch (\Exception $e) {
            Log::error('Error al generar PDF de factura', [
                'factura_id' => $facturaId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Alias para generatePDF (compatibilidad con controlador)
     * Genera PDF y retorna respuesta de descarga
     */
    public function generatePDF(int $facturaId)
    {
        $factura = $this->facturaRepository->findById($facturaId);
        
        if (!$factura) {
            throw new \Exception("Factura no encontrada con ID: {$facturaId}");
        }

        try {
            // Cargar las relaciones necesarias para el PDF
            $factura->load(['venta.detalles', 'venta.cliente']);
            
            // Verificar que la venta existe
            if (!$factura->venta) {
                throw new \Exception("La factura no tiene una venta asociada");
            }
            
            // Generar PDF usando DomPDF directamente para descarga
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.factura-electronica', [
                'factura' => $factura,
                'titulo' => 'Factura Electrónica #' . $factura->secuencial
            ]);
            
            // Configurar opciones del PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            // Generar nombre del archivo
            $numeroFactura = str_pad($factura->establecimiento, 3, '0', STR_PAD_LEFT) . '-' . 
                           str_pad($factura->punto_emision, 3, '0', STR_PAD_LEFT) . '-' . 
                           str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT);
            
            $nombreArchivo = "factura_electronica_{$numeroFactura}.pdf";
            
            Log::info('✅ PDF generado para descarga', [
                'factura_id' => $facturaId,
                'numero_factura' => $numeroFactura,
                'estado_sri' => $factura->estado_sri,
                'tiene_venta' => $factura->venta ? 'sí' : 'no'
            ]);
            
            // Retornar respuesta de descarga
            return $pdf->download($nombreArchivo);
            
        } catch (\Exception $e) {
            Log::error('❌ Error al generar PDF para descarga', [
                'factura_id' => $facturaId,
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => basename($e->getFile())
            ]);
            throw new \Exception("Error al generar PDF: " . $e->getMessage());
        }
    }

    /**
     * Obtener XML de la factura (documento o autorizado)
     */
    public function getXML(int $facturaId, string $tipo = 'autorizado'): string
    {
        $factura = $this->facturaRepository->findById($facturaId);
        
        if (!$factura) {
            throw new \Exception("Factura no encontrada con ID: {$facturaId}");
        }

        switch ($tipo) {
            case 'autorizado':
                if (empty($factura->xml_autorizado)) {
                    throw new \Exception("La factura no tiene XML autorizado disponible. Estado SRI: {$factura->estado_sri}");
                }
                return $factura->xml_autorizado;
                
            case 'documento':
                if (empty($factura->xml_documento)) {
                    throw new \Exception("La factura no tiene XML del documento disponible");
                }
                return $factura->xml_documento;
                
            default:
                throw new \Exception("Tipo de XML no válido. Use 'autorizado' o 'documento'");
        }
    }

    /**
     * Obtener estadísticas de facturación
     */
    public function getEstadisticas(): array
    {
        $stats = $this->facturaRepository->getStats();
        
        return [
            'total_facturas' => $stats['total'],
            'facturas_autorizadas' => $stats['autorizadas'],
            'facturas_rechazadas' => $stats['rechazadas'],
            'facturas_pendientes' => $stats['pendientes'],
            'facturas_eliminadas' => $stats['eliminadas'],
            'tasa_autorizacion' => $stats['total'] > 0 ? ($stats['autorizadas'] / $stats['total']) * 100 : 0,
            'facturacion_mensual' => $this->getFacturacionMensual(),
        ];
    }

    /**
     * Reenviar factura al SRI
     */
    public function reenviarAlSRI(int $facturaId): FacturaElectronica
    {
        $factura = $this->facturaRepository->findById($facturaId);
        
        if (!$factura) {
            throw new \Exception("Factura no encontrada con ID: {$facturaId}");
        }

        if ($factura->estado_sri === 'AUTORIZADA') {
            throw new \Exception("La factura ya está autorizada");
        }

        // Resetear estado a BORRADOR y volver a procesar
        $this->facturaRepository->updateEstadoSri($facturaId, 'BORRADOR');
        
        return $this->procesarConSRI($facturaId);
    }

    /**
     * Buscar facturas
     */
    public function buscar(string $termino): Collection
    {
        return $this->facturaRepository->search($termino);
    }

    /**
     * Generar XML del documento
     */
    private function generarXMLDocumento(FacturaElectronica $factura): void
    {
        // Aquí iría la lógica para generar el XML según especificaciones SRI
        $xml = $this->construirXMLFactura($factura);
        
        $this->facturaRepository->update($factura->factura_electronica_id, [
            'xml_documento' => $xml
        ]);
    }

    /**
     * Construir XML de la factura
     */
    private function construirXMLFactura(FacturaElectronica $factura): string
    {
        // Simulación de XML SRI Ecuador
        // En producción esto debe seguir exactamente el formato requerido por el SRI
        $venta = $factura->venta;
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<factura version="1.1.0">' . "\n";
        $xml .= '  <infoTributaria>' . "\n";
        $xml .= '    <ambiente>' . $factura->ambiente . '</ambiente>' . "\n";
        $xml .= '    <tipoEmision>' . $factura->tipo_emision . '</tipoEmision>' . "\n";
        $xml .= '    <razonSocial>' . htmlspecialchars($factura->razon_social_emisor) . '</razonSocial>' . "\n";
        $xml .= '    <ruc>' . $factura->ruc_emisor . '</ruc>' . "\n";
        $xml .= '    <estab>' . $factura->establecimiento . '</estab>' . "\n";
        $xml .= '    <ptoEmi>' . $factura->punto_emision . '</ptoEmi>' . "\n";
        $xml .= '    <secuencial>' . str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT) . '</secuencial>' . "\n";
        $xml .= '    <dirMatriz>' . htmlspecialchars($factura->direccion_emisor) . '</dirMatriz>' . "\n";
        $xml .= '  </infoTributaria>' . "\n";
        $xml .= '  <infoFactura>' . "\n";
        $xml .= '    <fechaEmision>' . $venta->fecha->format('d/m/Y') . '</fechaEmision>' . "\n";
        $xml .= '    <razonSocialComprador>' . htmlspecialchars($factura->razon_social_comprador) . '</razonSocialComprador>' . "\n";
        $xml .= '    <identificacionComprador>' . $factura->identificacion_comprador . '</identificacionComprador>' . "\n";
        $xml .= '    <totalSinImpuestos>' . number_format($venta->subtotal, 2, '.', '') . '</totalSinImpuestos>' . "\n";
        $xml .= '    <totalDescuento>0.00</totalDescuento>' . "\n";
        $xml .= '    <importeTotal>' . number_format($venta->total, 2, '.', '') . '</importeTotal>' . "\n";
        $xml .= '  </infoFactura>' . "\n";
        $xml .= '  <detalles>' . "\n";
        
        foreach ($venta->detalles as $detalle) {
            $xml .= '    <detalle>' . "\n";
            $xml .= '      <descripcion>' . htmlspecialchars($detalle->descripcion) . '</descripcion>' . "\n";
            $xml .= '      <cantidad>' . $detalle->cantidad . '</cantidad>' . "\n";
            $xml .= '      <precioUnitario>' . number_format($detalle->precio_unitario, 2, '.', '') . '</precioUnitario>' . "\n";
            $xml .= '      <precioTotalSinImpuesto>' . number_format($detalle->cantidad * $detalle->precio_unitario, 2, '.', '') . '</precioTotalSinImpuesto>' . "\n";
            $xml .= '    </detalle>' . "\n";
        }
        
        $xml .= '  </detalles>' . "\n";
        $xml .= '</factura>' . "\n";

        return $xml;
    }

    /**
     * Simular procesamiento con SRI
     */
    private function simularProcesarSRI(FacturaElectronica $factura): array
    {
        // Simulación de respuesta del SRI
        // En producción aquí iría la integración real con los web services del SRI
        
        // Simular que el 95% de las facturas se autorizan
        $autorizada = rand(1, 100) <= 95;
        
        if ($autorizada) {
            return [
                'autorizada' => true,
                'mensaje' => 'AUTORIZADA',
                'errores' => null
            ];
        } else {
            return [
                'autorizada' => false,
                'mensaje' => 'RECHAZADA - Error de validación',
                'errores' => [
                    ['codigo' => '001', 'mensaje' => 'Error simulado para testing']
                ]
            ];
        }
    }

    /**
     * Generar clave de acceso
     */
    private function generarClaveAcceso(FacturaElectronica $factura): string
    {
        // Formato: ddmmaaaaddmmaaaatipoDocumentoRucCodNumericoTipoEmisionDigitoVerificador
        $fecha = $factura->venta->fecha;
        $claveAcceso = $fecha->format('dmY');
        $claveAcceso .= '01'; // Tipo documento (factura)
        $claveAcceso .= $factura->ruc_emisor;
        $claveAcceso .= $factura->ambiente;
        $claveAcceso .= $factura->establecimiento . $factura->punto_emision;
        $claveAcceso .= str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT);
        $claveAcceso .= '12345678'; // Código numérico (8 dígitos aleatorios en producción)
        $claveAcceso .= $factura->tipo_emision;
        
        // Dígito verificador (Módulo 11)
        $claveAcceso .= $this->calcularDigitoVerificador($claveAcceso);
        
        return $claveAcceso;
    }

    /**
     * Calcular dígito verificador módulo 11
     */
    private function calcularDigitoVerificador(string $clave): int
    {
        $multiplicadores = [2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7];
        $suma = 0;
        
        for ($i = 0; $i < strlen($clave); $i++) {
            $suma += intval($clave[$i]) * $multiplicadores[$i];
        }
        
        $residuo = $suma % 11;
        $digitoVerificador = $residuo === 0 ? 0 : (11 - $residuo);
        
        return $digitoVerificador === 10 ? 1 : $digitoVerificador;
    }

    /**
     * Generar XML autorizado
     */
    private function generarXMLAutorizado(FacturaElectronica $factura, string $claveAcceso): string
    {
        // El XML autorizado incluye la clave de acceso y fecha de autorización
        $xml = $factura->xml_documento;
        
        // Si no hay XML documento, generar uno básico
        if (empty($xml)) {
            Log::warning('XML documento vacío, generando XML básico para autorización', [
                'factura_id' => $factura->factura_electronica_id
            ]);
            $xml = $this->construirXMLFactura($factura);
        }
        
        // Insertar clave de acceso en el XML si no la tiene
        if (strpos($xml, '<claveAcceso>') === false) {
            $xml = str_replace(
                '</infoTributaria>',
                '    <claveAcceso>' . $claveAcceso . '</claveAcceso>' . "\n" . '</infoTributaria>',
                $xml
            );
        }
        
        // Agregar información de autorización al XML
        $xml = str_replace(
            '</factura>',
            '  <infoAdicional>' . "\n" .
            '    <campoAdicional nombre="fechaAutorizacion">' . now()->format('d/m/Y H:i:s') . '</campoAdicional>' . "\n" .
            '    <campoAdicional nombre="numeroAutorizacion">' . ($factura->numero_autorizacion ?? 'N/A') . '</campoAdicional>' . "\n" .
            '  </infoAdicional>' . "\n" .
            '</factura>',
            $xml
        );
        
        return $xml;
    }

    /**
     * Generar archivo PDF
     */
    private function generarArchivoPDF(FacturaElectronica $factura, string $rutaPDF): void
    {
        try {
            // Cargar las relaciones necesarias para el PDF
            $factura->load(['venta.detalles', 'venta.cliente']);
            
            // Crear directorio si no existe
            $directorio = dirname($rutaPDF);
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }
            
            // Generar PDF usando DomPDF con la vista Blade
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.factura-electronica', [
                'factura' => $factura,
                'titulo' => 'Factura Electrónica #' . $factura->secuencial
            ]);
            
            // Configurar opciones del PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial'
            ]);
            
            // Guardar el PDF
            $pdf->save($rutaPDF);
            
            Log::info('✅ PDF generado correctamente', [
                'factura_id' => $factura->factura_electronica_id,
                'ruta' => $rutaPDF,
                'tamaño' => filesize($rutaPDF) . ' bytes'
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Error al generar PDF', [
                'factura_id' => $factura->factura_electronica_id,
                'error' => $e->getMessage(),
                'ruta' => $rutaPDF
            ]);
            
            // Fallback: crear un PDF simple con contenido básico
            $contenidoFallback = "FACTURA ELECTRONICA\n\n";
            $contenidoFallback .= "Número: " . str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT) . "\n";
            $contenidoFallback .= "Cliente: " . $factura->razon_social_comprador . "\n";
            $contenidoFallback .= "Total: $" . number_format($factura->total, 2) . "\n";
            $contenidoFallback .= "Estado SRI: " . $factura->estado_sri . "\n\n";
            $contenidoFallback .= "Error al generar PDF completo: " . $e->getMessage() . "\n";
            
            file_put_contents($rutaPDF, $contenidoFallback);
            throw $e;
        }
    }

    /**
     * Obtener facturación mensual
     */
    private function getFacturacionMensual(): array
    {
        $mesActual = now();
        $inicioMes = $mesActual->copy()->startOfMonth()->format('Y-m-d');
        $finMes = $mesActual->copy()->endOfMonth()->format('Y-m-d');
        
        return [
            'periodo' => $mesActual->format('Y-m'),
            'total_facturado' => $this->facturaRepository->getTotalVentasByDateRange($inicioMes, $finMes),
            'facturas_emitidas' => $this->facturaRepository->getByDateRange($inicioMes, $finMes)->count(),
        ];
    }
}
