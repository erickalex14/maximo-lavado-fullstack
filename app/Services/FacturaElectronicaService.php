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
                    'ruc_emisor' => $datosEmisor['ruc_emisor'] ?? config('facturacion.ruc_emisor'),
                    'razon_social_emisor' => $datosEmisor['razon_social_emisor'] ?? config('facturacion.razon_social'),
                    'direccion_emisor' => $datosEmisor['direccion_emisor'] ?? config('facturacion.direccion'),
                    'establecimiento' => $datosEmisor['establecimiento'] ?? '001',
                    'punto_emision' => $datosEmisor['punto_emision'] ?? '001',
                    
                    // Datos del comprador (snapshot para inmutabilidad SRI)
                    'identificacion_comprador' => $cliente->cedula ?? '9999999999999',
                    'razon_social_comprador' => $cliente->nombre,
                    'direccion_comprador' => $cliente->direccion,
                    'email_comprador' => $cliente->email,
                    
                    // Información del documento
                    'tipo_documento' => '01', // 01: Factura
                    'secuencial' => $secuencial,
                    'ambiente' => config('facturacion.ambiente', '1'), // 1: Pruebas, 2: Producción
                    'tipo_emision' => '1', // 1: Emisión normal
                    
                    // Estado inicial
                    'estado_sri' => 'BORRADOR',
                ];

                // Crear la factura
                $factura = $this->facturaRepository->create($datosFactura);

                // Generar XML del documento
                $this->generarXMLDocumento($factura);

                Log::info('Factura electrónica generada', [
                    'factura_id' => $factura->factura_electronica_id,
                    'venta_id' => $venta->venta_id,
                    'secuencial' => $secuencial,
                    'total' => $venta->total
                ]);

                return $factura;
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
        
        // Insertar clave de acceso en el XML
        $xml = str_replace(
            '</infoTributaria>',
            '    <claveAcceso>' . $claveAcceso . '</claveAcceso>' . "\n" . '</infoTributaria>',
            $xml
        );
        
        return $xml;
    }

    /**
     * Generar archivo PDF
     */
    private function generarArchivoPDF(FacturaElectronica $factura, string $rutaPDF): void
    {
        // Simulación de generación de PDF
        // En producción aquí se usaría una librería como DomPDF o similar
        $contenido = "Factura Electrónica #{$factura->secuencial}\n";
        $contenido .= "Cliente: {$factura->razon_social_comprador}\n";
        $contenido .= "Total: $" . number_format($factura->venta->total, 2) . "\n";
        
        // Crear directorio si no existe
        $directorio = dirname($rutaPDF);
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        file_put_contents($rutaPDF, $contenido);
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
