<?php

namespace App\Services\SRI;

use SoapClient;
use SoapFault;

class SRIClient
{
    private string $recepcionWsdl;
    private string $autorizacionWsdl;
    private int $timeout;

    public function __construct()
    {
    $ambiente = config('sri.ambiente') === '2' ? 'produccion' : 'pruebas';
    $urls = config('sri.urls.' . $ambiente);
        $this->recepcionWsdl = $urls['recepcion'];
        $this->autorizacionWsdl = $urls['autorizacion'];
        $this->timeout = (int) config('sri.timeout.respuesta', 120);
    }

    public function enviarComprobante(string $xmlFirmado): array
    {
        try {
            $client = new SoapClient($this->recepcionWsdl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => $this->timeout,
            ]);

            // Enviar contenido como base64Binary SIN doble codificación.
            // SoapClient se encargará de codificar en base64 según el tipo XSD.
            $params = ['xml' => new \SoapVar($xmlFirmado, XSD_BASE64BINARY)];
            $response = $client->__soapCall('validarComprobante', [$params]);

            // Opcional: guardar request/response para depuración
            try {
                if ((bool) config('sri.debug.guardar_xml_request', false) || (bool) config('sri.debug.guardar_xml_response', false)) {
                    $dbgDir = storage_path('app/sri/debug');
                    if (!is_dir($dbgDir)) { @mkdir($dbgDir, 0755, true); }
                    $ts = date('Ymd_His');
                    if ((bool) config('sri.debug.guardar_xml_request', false)) {
                        @file_put_contents($dbgDir . DIRECTORY_SEPARATOR . "recepcion_request_{$ts}.xml", $client->__getLastRequest());
                    }
                    if ((bool) config('sri.debug.guardar_xml_response', false)) {
                        @file_put_contents($dbgDir . DIRECTORY_SEPARATOR . "recepcion_response_{$ts}.xml", $client->__getLastResponse());
                    }
                }
            } catch (\Throwable $e) { /* ignore debug save errors */ }

            $estado = $response->RespuestaRecepcionComprobante->estado ?? 'DEVUELTA';
            $mensajes = $response->RespuestaRecepcionComprobante->comprobantes->comprobante->mensajes->mensaje ?? [];

            return [
                'estado' => $estado,
                'mensajes' => $mensajes,
                'raw' => $response,
            ];
        } catch (SoapFault $e) {
            return [
                'estado' => 'ERROR',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function autorizarComprobante(string $claveAcceso): array
    {
        try {
            $client = new SoapClient($this->autorizacionWsdl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => $this->timeout,
            ]);

            $params = ['claveAccesoComprobante' => $claveAcceso];
            $response = $client->__soapCall('autorizacionComprobante', [$params]);

            // Opcional: guardar request/response para depuración
            try {
                if ((bool) config('sri.debug.guardar_xml_request', false) || (bool) config('sri.debug.guardar_xml_response', false)) {
                    $dbgDir = storage_path('app/sri/debug');
                    if (!is_dir($dbgDir)) { @mkdir($dbgDir, 0755, true); }
                    $ts = date('Ymd_His');
                    if ((bool) config('sri.debug.guardar_xml_request', false)) {
                        @file_put_contents($dbgDir . DIRECTORY_SEPARATOR . "autorizacion_request_{$ts}.xml", $client->__getLastRequest());
                    }
                    if ((bool) config('sri.debug.guardar_xml_response', false)) {
                        @file_put_contents($dbgDir . DIRECTORY_SEPARATOR . "autorizacion_response_{$ts}.xml", $client->__getLastResponse());
                    }
                }
            } catch (\Throwable $e) { /* ignore debug save errors */ }

            $autorizaciones = $response->RespuestaAutorizacionComprobante->autorizaciones->autorizacion ?? null;
            if (is_array($autorizaciones)) {
                $autorizacion = $autorizaciones[0];
            } else {
                $autorizacion = $autorizaciones;
            }

            if (!$autorizacion) {
                return [
                    'estado' => 'PENDIENTE',
                    'mensaje' => 'Sin autorizaciones devueltas',
                    'raw' => $response,
                ];
            }

            $estado = $autorizacion->estado ?? 'PENDIENTE';
            $numeroAutorizacion = $autorizacion->numeroAutorizacion ?? null;
            $fechaAutorizacion = $autorizacion->fechaAutorizacion ?? null;
            $comprobante = isset($autorizacion->comprobante) ? (is_string($autorizacion->comprobante) ? $autorizacion->comprobante : '') : '';

            return [
                'estado' => $estado,
                'numeroAutorizacion' => $numeroAutorizacion,
                'fechaAutorizacion' => $fechaAutorizacion,
                'xmlAutorizado' => $comprobante,
                'raw' => $response,
            ];
        } catch (SoapFault $e) {
            return [
                'estado' => 'ERROR',
                'error' => $e->getMessage(),
            ];
        }
    }
}
