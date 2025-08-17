<?php

namespace App\Services;

use App\Contracts\FacturaElectronicaRepositoryInterface;
use App\Models\FacturaElectronica;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SRI\XMLSigner;
use App\Services\SRI\SRIClient;

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
                // Integración real con SRI si no está en modo mock
                $usarMock = (bool) config('sri.debug.mock_sri_response', true);
                if (!$usarMock) {
                    try {
                        // 1) Asegurar XML del comprobante y clave de acceso
                        if (empty($factura->xml_documento)) {
                            $this->generarXMLDocumento($factura);
                            $factura = $this->facturaRepository->findById($factura->factura_electronica_id);
                        }
                        // Asegurar sincronía de claveAcceso entre XML y base de datos
                        $xmlActual = $factura->xml_documento;
                        $claveAcceso = null;
                        if (preg_match('/<claveAcceso>([^<]+)<\/claveAcceso>/', $xmlActual, $m)) {
                            $claveAcceso = $m[1];
                        }
                        if (!$claveAcceso) {
                            $claveAcceso = $this->generarClaveAcceso($factura);
                            $xmlConClave = str_replace(
                                '</infoTributaria>',
                                '    <claveAcceso>' . $claveAcceso . '</claveAcceso>' . "\n" . '</infoTributaria>',
                                $xmlActual
                            );
                            $factura = $this->facturaRepository->update($factura->factura_electronica_id, [
                                'xml_documento' => $xmlConClave,
                                'clave_acceso' => $claveAcceso,
                            ]);
                        } else {
                            $factura = $this->facturaRepository->update($factura->factura_electronica_id, [
                                'clave_acceso' => $claveAcceso,
                            ]);
                        }

                        // 2) Firmar XML
                        $signer = new XMLSigner();
                        $xmlFirmado = $signer->sign($factura->xml_documento);
                        // Guardar XML firmado para inspección/envío
                        $signedDir = storage_path('app/sri/signed');
                        if (!is_dir($signedDir)) { @mkdir($signedDir, 0755, true); }
                        $signedPath = $signedDir . DIRECTORY_SEPARATOR . 'factura_' . $factura->factura_electronica_id . '_firmada.xml';
                        @file_put_contents($signedPath, $xmlFirmado);
                        // Guardar XML firmado para diagnóstico
                        try {
                            $signedDir = storage_path('app/sri/signed');
                            if (!is_dir($signedDir)) { @mkdir($signedDir, 0755, true); }
                            $signedName = 'factura_' . $factura->factura_electronica_id . '_firmada.xml';
                            file_put_contents($signedDir . DIRECTORY_SEPARATOR . $signedName, $xmlFirmado);
                        } catch (\Throwable $eSave) {
                            Log::warning('No se pudo guardar XML firmado para diagnóstico', [
                                'error' => $eSave->getMessage()
                            ]);
                        }

                        // 3) Enviar a SRI Recepción
                        $sri = new SRIClient();
                        $respRecep = $sri->enviarComprobante($xmlFirmado);

                        if (($respRecep['estado'] ?? '') === 'RECIBIDA') {
                            // 4) Consultar Autorización
                            // pequeño backoff
                            usleep(500000);
                            $respAuth = $sri->autorizarComprobante($factura->clave_acceso);

                            if (($respAuth['estado'] ?? '') === 'AUTORIZADO') {
                                $xmlAut = $respAuth['xmlAutorizado'] ?: $this->generarXMLAutorizado($factura, $factura->clave_acceso);
                                $this->facturaRepository->update($factura->factura_electronica_id, [
                                    'estado_sri' => 'AUTORIZADA',
                                    'numero_autorizacion' => $respAuth['numeroAutorizacion'] ?? null,
                                    'fecha_autorizacion' => now(),
                                    'xml_autorizado' => $xmlAut,
                                    'mensaje_sri' => 'AUTORIZADA',
                                    'errores_sri' => null,
                                ]);
                            } else {
                                $this->facturaRepository->update($factura->factura_electronica_id, [
                                    'estado_sri' => 'RECHAZADA',
                                    'mensaje_sri' => $respAuth['error'] ?? ($respAuth['mensaje'] ?? 'No autorizado'),
                                    'errores_sri' => [],
                                ]);
                            }
                        } else {
                            $this->facturaRepository->update($factura->factura_electronica_id, [
                                'estado_sri' => 'RECHAZADA',
                                'mensaje_sri' => $respRecep['error'] ?? 'DEVUELTA',
                                'errores_sri' => $respRecep['mensajes'] ?? [],
                            ]);
                        }

                        return $this->facturaRepository->findById($factura->factura_electronica_id);
                    } catch (\Throwable $e) {
                        // No lanzar 500: marcar como RECHAZADA y guardar mensaje
                        $this->facturaRepository->update($factura->factura_electronica_id, [
                            'estado_sri' => 'RECHAZADA',
                            'mensaje_sri' => 'Error en firma/envío SRI: ' . $e->getMessage(),
                            'errores_sri' => [
                                ['codigo' => 'EX', 'mensaje' => $e->getMessage()]
                            ],
                        ]);
                        return $this->facturaRepository->findById($factura->factura_electronica_id);
                    }
                }

                // Fallback: simulación
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
            // Asegurar estado y datos actualizados (puede venir de una transacción de venta)
            $factura = $this->facturaRepository->findById($factura->factura_electronica_id) ?? $factura;

            // Si ya no está en BORRADOR (por ejemplo, reintento), no reprocesar
            if ($factura->estado_sri !== 'BORRADOR') {
                Log::info('⏭️ Omitido procesamiento automático: estado no BORRADOR', [
                    'factura_id' => $factura->factura_electronica_id,
                    'estado_sri' => $factura->estado_sri,
                ]);
                return $factura;
            }

            $usarMock = (bool) config('sri.debug.mock_sri_response', true);
            if (!$usarMock) {
                // Delegar a la integración real (Recepción + Autorización)
                return $this->procesarConSRI($factura->factura_electronica_id);
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
     * Validar configuración de SRI (certificado y clave) y devolver diagnóstico claro
     */
    public function validarConexionSRI(): array
    {
        $p12PathCfg = config('sri.certificado.archivo');
        $p12Path = base_path(trim((string)$p12PathCfg, "\"' "));
        $pwd = trim((string) config('sri.certificado.clave', ''), "\"' ");
        $result = [
            'ambiente' => config('sri.ambiente'),
            'p12' => [
                'config' => $p12PathCfg,
                'resolved' => $p12Path,
                'exists' => file_exists($p12Path),
                'filesize' => @filesize($p12Path) ?: 0,
                'read_ok' => false,
                'pkcs12_ok' => false,
                'mensaje' => null,
            ],
            'pem' => [
                'key' => config('sri.certificado.pem_key'),
                'cert' => config('sri.certificado.pem_cert'),
                'key_exists' => null,
                'cert_exists' => null,
                'usable' => false,
            ],
        ];

        // Probar P12
        try {
            if ($result['p12']['exists']) {
                $content = @file_get_contents($p12Path);
                $result['p12']['read_ok'] = $content !== false;
                if ($content !== false) {
                    $arr = [];
                    $ok = @openssl_pkcs12_read($content, $arr, $pwd);
                    $result['p12']['pkcs12_ok'] = (bool) $ok;
                    if (!$ok) {
                        $errors = [];
                        while ($e = openssl_error_string()) { $errors[] = $e; }
                        $result['p12']['mensaje'] = count($errors) ? implode(' | ', $errors) : 'Fallo al leer PKCS12 (clave/algoritmo)';
                    } else {
                        $result['p12']['mensaje'] = 'OK';
                    }
                } else {
                    $result['p12']['mensaje'] = 'No se pudo leer el archivo';
                }
            } else {
                $result['p12']['mensaje'] = 'Archivo no existe';
            }
        } catch (\Throwable $e) {
            $result['p12']['mensaje'] = $e->getMessage();
        }

        // Probar PEM (si están configurados)
    $pemKey = config('sri.certificado.pem_key');
    $pemCert = config('sri.certificado.pem_cert');
        if ($pemKey && $pemCert) {
            $pemKeyPath = base_path(trim((string)$pemKey, "\"' "));
            $pemCertPath = base_path(trim((string)$pemCert, "\"' "));
            $result['pem']['key_exists'] = file_exists($pemKeyPath);
            $result['pem']['cert_exists'] = file_exists($pemCertPath);
            $keyContent = $result['pem']['key_exists'] ? @file_get_contents($pemKeyPath) : false;
            $crtContent = $result['pem']['cert_exists'] ? @file_get_contents($pemCertPath) : false;
            $result['pem']['usable'] = ($keyContent && $crtContent);
        }

        // Resumen legible
        $ok = ($result['p12']['pkcs12_ok'] === true) || ($result['pem']['usable'] === true);
        return [
            'success' => $ok,
            'detalle' => $result,
            'mensaje' => $ok
                ? 'Certificados listos para firmar (P12 o PEM)'
                : 'No se pudo usar P12 ni PEM. Revise clave, archivo o habilite legacy provider/PEM.'
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
     * Regenerar y actualizar el XML del documento para una factura específica.
     * Retorna la entidad actualizada.
     */
    public function regenerarXMLDocumento(int $facturaId): FacturaElectronica
    {
        $factura = $this->facturaRepository->findById($facturaId);
        if (!$factura) {
            throw new \RuntimeException("Factura no encontrada con ID: {$facturaId}");
        }
        $xml = $this->construirXMLFactura($factura);
        $this->facturaRepository->update($factura->factura_electronica_id, [
            'xml_documento' => $xml,
        ]);
        return $this->facturaRepository->findById($factura->factura_electronica_id);
    }

    /**
     * Construir XML de la factura
     */
    private function construirXMLFactura(FacturaElectronica $factura): string
    {
    // Construcción de XML SRI Ecuador (versión 1.1.0)
    // Nota: Simplificado pero con estructura clave obligatoria
        $venta = $factura->venta;
    $secuencial = str_pad($factura->secuencial, 9, '0', STR_PAD_LEFT);
    $codDoc = '01';
    $dirEstablecimiento = htmlspecialchars(config('sri.direccion_establecimiento'));
    $ruc = $factura->ruc_emisor;
    $razonSocial = htmlspecialchars($factura->razon_social_emisor);
    $dirMatriz = htmlspecialchars(config('sri.direccion_matriz'));
    $fechaRaw = $venta->fecha ?? now();
    if ($fechaRaw instanceof \DateTimeInterface) {
        $fechaEmision = $fechaRaw->format('d/m/Y');
    } else {
        try { $fechaEmision = \Carbon\Carbon::parse($fechaRaw)->format('d/m/Y'); }
        catch (\Throwable $e) { $fechaEmision = now()->format('d/m/Y'); }
    }
    // Calcular totales en base a los detalles para asegurar consistencia con SRI
    $detallesVenta = $venta->detalles ?? [];
    $detallesArray = is_iterable($detallesVenta) ? $detallesVenta : [];
    $sumBase = 0.0;
    $sumDescuento = 0.0;
    foreach ($detallesArray as $d) {
        $cant = (float) ($d->cantidad ?? 0);
        $pu = (float) ($d->precio_unitario ?? 0);
        $desc = (float) ($d->descuento ?? 0);
        $lineaBase = ($cant * $pu) - $desc;
        if ($lineaBase < 0) { $lineaBase = 0; }
        $sumBase += $lineaBase;
        $sumDescuento += $desc;
    }
    // Si no hay detalles o sumBase es cero, usar subtotal/iva/total de la venta como respaldo
    if ($sumBase <= 0 && isset($venta->subtotal)) {
        $sumBase = (float) $venta->subtotal;
        $sumDescuento = (float) ($venta->descuento ?? 0);
    }
    $ivaTotal = round($sumBase * 0.15, 2);
    $totalSinImpuestos = number_format($sumBase, 2, '.', '');
    $importeTotal = number_format($sumBase + $ivaTotal, 2, '.', '');
    $propina = '0.00';
    $moneda = 'DOLAR'; // SRI admite texto libre; se mantiene "DOLAR"
    $identComprador = $factura->identificacion_comprador;
    $razonComprador = htmlspecialchars($factura->razon_social_comprador);
    $claveAcceso = $this->generarClaveAcceso($factura);
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<factura id="comprobante" version="1.1.0">' . "\n";
        $xml .= '  <infoTributaria>' . "\n";
        $xml .= '    <ambiente>' . $factura->ambiente . '</ambiente>' . "\n";
        $xml .= '    <tipoEmision>' . $factura->tipo_emision . '</tipoEmision>' . "\n";
        $xml .= '    <razonSocial>' . $razonSocial . '</razonSocial>' . "\n";
        $nombreComercial = config('sri.nombre_comercial');
        if (!empty($nombreComercial)) {
            $xml .= '    <nombreComercial>' . htmlspecialchars($nombreComercial) . '</nombreComercial>' . "\n";
        }
        $xml .= '    <ruc>' . $ruc . '</ruc>' . "\n";
        $xml .= '    <claveAcceso>' . $claveAcceso . '</claveAcceso>' . "\n";
        $xml .= '    <codDoc>' . $codDoc . '</codDoc>' . "\n";
        $xml .= '    <estab>' . $factura->establecimiento . '</estab>' . "\n";
        $xml .= '    <ptoEmi>' . $factura->punto_emision . '</ptoEmi>' . "\n";
        $xml .= '    <secuencial>' . $secuencial . '</secuencial>' . "\n";
        $xml .= '    <dirMatriz>' . $dirMatriz . '</dirMatriz>' . "\n";
        // Opcional: contribuyenteRimpe (cuando aplique)
    $rimpe = config('sri.contribuyente_rimpe', null);
        if (!empty($rimpe)) {
            $xml .= '    <contribuyenteRimpe>' . htmlspecialchars($rimpe) . '</contribuyenteRimpe>' . "\n";
        }
        $xml .= '  </infoTributaria>' . "\n";
        $xml .= '  <infoFactura>' . "\n";
        $xml .= '    <fechaEmision>' . $fechaEmision . '</fechaEmision>' . "\n";
        $xml .= '    <dirEstablecimiento>' . $dirEstablecimiento . '</dirEstablecimiento>' . "\n";
        // No agregar campos no estándares como <lugar> para cumplir XSD del SRI
        $xml .= '    <obligadoContabilidad>NO</obligadoContabilidad>' . "\n";
        $xml .= '    <tipoIdentificacionComprador>' . $this->tipoIdentificacion($identComprador) . '</tipoIdentificacionComprador>' . "\n";
        $xml .= '    <razonSocialComprador>' . $razonComprador . '</razonSocialComprador>' . "\n";
        $xml .= '    <identificacionComprador>' . $identComprador . '</identificacionComprador>' . "\n";
        if (!empty($factura->direccion_comprador)) {
            $xml .= '    <direccionComprador>' . htmlspecialchars($factura->direccion_comprador) . '</direccionComprador>' . "\n";
        }
        $xml .= '    <totalSinImpuestos>' . $totalSinImpuestos . '</totalSinImpuestos>' . "\n";
        $xml .= '    <totalDescuento>' . number_format($sumDescuento, 2, '.', '') . '</totalDescuento>' . "\n";
        // Totales por impuesto (único bloque IVA estándar)
        $codigos = (array) config('sri.iva.codigo_porcentaje', []);
        $ivaPercent = array_key_exists(15, $codigos) ? 15 : (array_key_exists(12, $codigos) ? 12 : 12);
        $codigoPorcentaje = (string) ($codigos[$ivaPercent] ?? '2');
        $ivaRateDec = $ivaPercent / 100;
        $ivaTotalCalc = number_format($sumBase * $ivaRateDec, 2, '.', '');
    $xml .= '    <totalConImpuestos>' . "\n";
    $xml .= '      <totalImpuesto>' . "\n";
    $xml .= '        <codigo>2</codigo>' . "\n"; // IVA
    $xml .= '        <codigoPorcentaje>' . $codigoPorcentaje . '</codigoPorcentaje>' . "\n";
    // Orden según XSD V1.1.0: baseImponible -> tarifa? -> valor
    $xml .= '        <baseImponible>' . $totalSinImpuestos . '</baseImponible>' . "\n";
    // tarifa es opcional en totales, pero varios validadores la exigen si hay IVA distinto de 0
    $xml .= '        <tarifa>' . number_format($ivaPercent, 2, '.', '') . '</tarifa>' . "\n";
    $xml .= '        <valor>' . $ivaTotalCalc . '</valor>' . "\n";
    $xml .= '      </totalImpuesto>' . "\n";
    $xml .= '    </totalConImpuestos>' . "\n";
        $xml .= '    <propina>' . $propina . '</propina>' . "\n";
        $xml .= '    <importeTotal>' . $importeTotal . '</importeTotal>' . "\n";
        $xml .= '    <moneda>' . $moneda . '</moneda>' . "\n";
        // Pagos (requerido en 1.1.0)
        $xml .= '    <pagos>' . "\n";
        $xml .= '      <pago>' . "\n";
        $xml .= '        <formaPago>01</formaPago>' . "\n"; // 01: Sin utilización del sistema financiero (contado)
        $xml .= '        <total>' . $importeTotal . '</total>' . "\n";
        $xml .= '      </pago>' . "\n";
        $xml .= '    </pagos>' . "\n";
        $xml .= '  </infoFactura>' . "\n";
        $xml .= '  <detalles>' . "\n";
        
        $itemIndex = 1;
        foreach ($detallesArray as $detalle) {
            $desc = $detalle->descripcion ?? ($detalle->item_descripcion ?? ($detalle->item_nombre ?? 'Item'));
            $desc = mb_substr((string) $desc, 0, 300);
            $baseItem = ($detalle->subtotal ?? (($detalle->cantidad ?? 0) * ($detalle->precio_unitario ?? 0))) - ($detalle->descuento ?? 0);
            if ($baseItem < 0) { $baseItem = 0; }
            $precioSinImp = number_format($baseItem, 2, '.', '');
            $valorIvaItem = number_format($baseItem * $ivaRateDec, 2, '.', '');
            $xml .= '    <detalle>' . "\n";
            $codigoPrincipal = $detalle->codigo ?? $detalle->sku ?? $detalle->codigo_principal ?? ('ITEM-' . $itemIndex);
            $xml .= '      <codigoPrincipal>' . htmlspecialchars((string) $codigoPrincipal) . '</codigoPrincipal>' . "\n";
            if (!empty($detalle->codigo_auxiliar)) {
                $xml .= '      <codigoAuxiliar>' . htmlspecialchars((string) $detalle->codigo_auxiliar) . '</codigoAuxiliar>' . "\n";
            }
            $xml .= '      <descripcion>' . htmlspecialchars($desc) . '</descripcion>' . "\n";
            $xml .= '      <cantidad>' . number_format((float) ($detalle->cantidad ?? 0), 2, '.', '') . '</cantidad>' . "\n";
            $xml .= '      <precioUnitario>' . number_format((float) ($detalle->precio_unitario ?? 0), 2, '.', '') . '</precioUnitario>' . "\n";
            $xml .= '      <descuento>' . number_format((float) ($detalle->descuento ?? 0), 2, '.', '') . '</descuento>' . "\n";
            $xml .= '      <precioTotalSinImpuesto>' . $precioSinImp . '</precioTotalSinImpuesto>' . "\n";
            $xml .= '      <impuestos>' . "\n";
            $xml .= '        <impuesto>' . "\n";
            $xml .= '          <codigo>2</codigo>' . "\n"; // IVA
            $xml .= '          <codigoPorcentaje>' . $codigoPorcentaje . '</codigoPorcentaje>' . "\n";
            $xml .= '          <tarifa>' . number_format($ivaPercent, 2, '.', '') . '</tarifa>' . "\n";
            $xml .= '          <baseImponible>' . $precioSinImp . '</baseImponible>' . "\n";
            $xml .= '          <valor>' . $valorIvaItem . '</valor>' . "\n";
            $xml .= '        </impuesto>' . "\n";
            $xml .= '      </impuestos>' . "\n";
            $xml .= '    </detalle>' . "\n";
            $itemIndex++;
        }
        
        $xml .= '  </detalles>' . "\n";
        // infoAdicional opcional
        $xml .= '  <infoAdicional>' . "\n";
        if (!empty($factura->email_comprador)) {
            $xml .= '    <campoAdicional nombre="Email">' . htmlspecialchars($factura->email_comprador) . '</campoAdicional>' . "\n";
        }
        if (!empty($factura->direccion_comprador)) {
            $xml .= '    <campoAdicional nombre="Direccion">' . htmlspecialchars($factura->direccion_comprador) . '</campoAdicional>' . "\n";
        }
        $xml .= '  </infoAdicional>' . "\n";
        $xml .= '</factura>' . "\n";

        return $xml;
    }

    private function tipoIdentificacion(string $id): string
    {
        $len = strlen($id);
        if ($id === '9999999999999') return '07';
        if ($len === 10) return '05'; // cédula
        if ($len === 13) return '04'; // RUC
        return '06'; // pasaporte/otros
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
    $claveAcceso .= (string) random_int(10000000, 99999999); // Código numérico (8 dígitos aleatorios)
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
        $multiplicador = 2;
        $suma = 0;
        for ($i = strlen($clave) - 1; $i >= 0; $i--) {
            $suma += intval($clave[$i]) * $multiplicador;
            $multiplicador++;
            if ($multiplicador > 7) { $multiplicador = 2; }
        }
        $residuo = $suma % 11;
        $digito = 11 - $residuo;
        if ($digito === 11) return 0;
        if ($digito === 10) return 1;
        return $digito;
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
