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

            $params = ['xml' => base64_encode($xmlFirmado)];
            $response = $client->__soapCall('validarComprobante', [$params]);

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
