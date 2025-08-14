<?php

namespace App\Services\SRI;

use DOMDocument;
use Illuminate\Support\Facades\Log;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class XMLSigner
{
    private string $certPath;
    private string $certPassword;

    public function __construct(?string $certPath = null, ?string $certPassword = null)
    {
        $this->certPath = $certPath ?? config('sri.certificado.archivo');
    $pwd = $certPassword ?? config('sri.certificado.clave');
    // Sanitizar: remover comillas/espacios accidentales alrededor
    $this->certPassword = is_string($pwd) ? trim($pwd, "\"' ") : $pwd;
    }

    /**
     * Firma XML en modalidad enveloped (XMLDSIG) para SRI.
     * Nota: SRI acepta firmas XMLDSIG enveloped sobre el nodo raíz del comprobante.
     */
    public function sign(string $xml, string $rootXPath = '/factura'): string
    {
        // Resolver ruta del certificado (soporta rutas relativas dentro del proyecto)
        $resolvedPath = $this->resolvePath($this->certPath);
        $privateKey = null;
        $publicCert = null;

        // 1) Preferir PEM si está configurado y legible
        $pemKeyPathCfg = config('sri.certificado.pem_key');
        $pemCertPathCfg = config('sri.certificado.pem_cert', config('sri.certificado.pem'));
        if ($pemKeyPathCfg && $pemCertPathCfg) {
            $pemKeyPath = $this->resolvePath($pemKeyPathCfg);
            $pemCertPath = $this->resolvePath($pemCertPathCfg);
            $pemKey = @file_get_contents($pemKeyPath);
            $pemCert = @file_get_contents($pemCertPath);
            Log::info('Chequeo PEM para firma', [
                'pem_key_cfg' => $pemKeyPathCfg,
                'pem_cert_cfg' => $pemCertPathCfg,
                'pem_key_resolved' => $pemKeyPath,
                'pem_cert_resolved' => $pemCertPath,
                'pem_key_exists' => file_exists($pemKeyPath),
                'pem_cert_exists' => file_exists($pemCertPath),
                'pem_key_len' => $pemKey !== false ? strlen($pemKey) : 0,
                'pem_cert_len' => $pemCert !== false ? strlen($pemCert) : 0,
            ]);
            if ($pemKey && $pemCert) {
                $privateKey = $pemKey;
                $publicCert = $pemCert;
                Log::info('Usando certificados PEM para firma SRI', [
                    'key' => $pemKeyPathCfg,
                    'cert' => $pemCertPathCfg,
                ]);
            } else if ($pemKeyPathCfg || $pemCertPathCfg) {
                Log::warning('PEM configurado pero no legible. Se intentará PKCS12.', [
                    'pem_key' => $pemKeyPathCfg,
                    'pem_cert' => $pemCertPathCfg,
                ]);
            }
        }

        // 2) Si no hay PEM usable, intentar PKCS12
        if (!$privateKey || !$publicCert) {
            if (!empty($resolvedPath) && file_exists($resolvedPath)) {
                if ($this->certPassword === null) {
                    throw new \RuntimeException('Clave del certificado digital no configurada');
                }

                $pkcs12 = @file_get_contents($resolvedPath);
                $certs = [];
                if ($pkcs12 === false) {
                    throw new \RuntimeException('No se pudo leer el archivo de certificado: ' . $resolvedPath);
                }
                if (openssl_pkcs12_read($pkcs12, $certs, $this->certPassword)) {
                    $privateKey = $certs['pkey'] ?? null;
                    $publicCert = $certs['cert'] ?? null;
                } else {
                    $osslErrors = [];
                    while ($e = openssl_error_string()) { $osslErrors[] = $e; }
                    Log::error('Fallo al leer PKCS12 y no hay PEM usable', [
                        'resolved_path' => $resolvedPath,
                        'filesize' => @filesize($resolvedPath),
                        'openssl_loaded' => extension_loaded('openssl'),
                        'openssl_errors' => $osslErrors,
                    ]);
                }
            } else {
                Log::warning('Archivo .p12 no encontrado y no hay PEM usable', [
                    'config_path' => $this->certPath,
                    'resolved_path' => $resolvedPath,
                ]);
            }
        }

        if (!$privateKey || !$publicCert) {
            Log::error('No se pudo cargar PEM ni PKCS12 para firmar', [
                'p12_path' => $resolvedPath,
                'pem_key_cfg' => $pemKeyPathCfg,
                'pem_cert_cfg' => $pemCertPathCfg,
            ]);
            throw new \RuntimeException('No se pudo obtener clave privada y certificado para firmar (PKCS12 o PEM).');
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        $doc->loadXML($xml);

        $dsig = new XMLSecurityDSig();
        $dsig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        // Agregar referencia al nodo raíz con ID para cumplir SRI (URI="#comprobante")
        $root = $doc->documentElement; // e.g., <factura id="comprobante">
        // Asegurar atributo id="comprobante" existe (SRI espera este Id)
        if (!$root->hasAttribute('id') || $root->getAttribute('id') === '') {
            $root->setAttribute('id', 'comprobante');
        }
        $dsig->addReference(
            $root,
            XMLSecurityDSig::SHA1,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['id_name' => 'id', 'overwrite' => false]
        );

        // Crear clave firma y firmar
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
    $key->loadKey($privateKey, false);
        $dsig->sign($key);

        // Adjuntar certificado X509
        $dsig->add509Cert($publicCert, true, false, ['subjectName' => true]);

    // Anexar firma al nodo raíz
    $dsig->appendSignature($root);

    // Devolver el XML completo firmado; SRI espera el documento con declaración XML
    return $doc->saveXML();
    }

    /**
     * Convierte una ruta relativa (por ejemplo, "storage/certificates/x.p12") en absoluta usando base_path.
     * Si ya es absoluta, la retorna sin cambios.
     */
    private function resolvePath(string $path): string
    {
        $trimmed = trim($path, "\"' ");
        // Windows absolute path (C:\...), UNC (\\server\share) o Unix absoluto (/...)
        $isAbsolute = preg_match('/^[a-zA-Z]:\\\\|^\\\\|^\//', $trimmed) === 1;
        return $isAbsolute ? $trimmed : base_path($trimmed);
    }
}
