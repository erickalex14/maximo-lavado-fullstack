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

        // Sanitizar: eliminar espacios y comillas accidentales
        $this->certPassword = is_string($pwd) ? trim($pwd, "\"' ") : $pwd;
    }

    /**
     * Firma XML en modalidad enveloped (XMLDSIG) para el SRI.
     * @param string $xml XML a firmar
     * @param string $rootXPath Etiqueta raíz del comprobante (ej: "/factura", "/notaCredito")
     * @return string XML firmado
     */
    public function sign(string $xml, string $rootXPath = '/factura'): string
    {
        $resolvedPath = $this->resolvePath($this->certPath);
        $privateKey = null;
        $publicCert = null;

        // 🔑 1) Cargar PEM si existe
        $pemKeyPathCfg = config('sri.certificado.pem_key');
        $pemCertPathCfg = config('sri.certificado.pem_cert');
        if ($pemKeyPathCfg && $pemCertPathCfg) {
            $pemKeyPath = $this->resolvePath($pemKeyPathCfg);
            $pemCertPath = $this->resolvePath($pemCertPathCfg);

            $pemKey = @file_get_contents($pemKeyPath);
            $pemCert = @file_get_contents($pemCertPath);

            if ($pemKey && $pemCert) {
                $privateKey = $pemKey;
                $publicCert = $pemCert;
            }
        }

        // 🔑 2) Si no hay PEM, intentar PKCS12 (.p12)
        if (!$privateKey || !$publicCert) {
            if (!file_exists($resolvedPath)) {
                throw new \RuntimeException("Certificado .p12 no encontrado: {$resolvedPath}");
            }

            $pkcs12 = @file_get_contents($resolvedPath);
            if ($pkcs12 === false) {
                throw new \RuntimeException("No se pudo leer el certificado: {$resolvedPath}");
            }

            $certs = [];
            if (!openssl_pkcs12_read($pkcs12, $certs, $this->certPassword)) {
                $osslErrors = [];
                while ($e = openssl_error_string()) { $osslErrors[] = $e; }
                throw new \RuntimeException("Error al leer PKCS12: " . implode(" | ", $osslErrors));
            }

            $privateKey = $certs['pkey'] ?? null;
            $publicCert = $certs['cert'] ?? null;
        }

        if (!$privateKey || !$publicCert) {
            throw new \RuntimeException("No se pudo obtener clave privada y certificado.");
        }

        // 📄 Cargar XML
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;
        $doc->loadXML($xml);

        // 🔖 Firma digital
        $dsig = new XMLSecurityDSig();
        $canonMethod = config('sri.firma.metodo_canonicalizacion') === 'http://www.w3.org/2001/10/xml-exc-c14n#'
            ? XMLSecurityDSig::EXC_C14N
            : XMLSecurityDSig::C14N;
        $dsig->setCanonicalMethod($canonMethod);

        // Nodo raíz
        $root = $doc->documentElement;


        // Referencia SHA256
        $dsig->addReference(
            $root,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['id_name' => 'id', 'overwrite' => false]
        );

        // Clave privada
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $key->loadKey($privateKey, false);

        // Firmar
        $dsig->sign($key);

        // Incluir certificado (sin cadena completa)
        $dsig->add509Cert($publicCert, true);

        // Insertar firma como último hijo
        $dsig->appendSignature($root);

        // Retornar XML firmado
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . $doc->saveXML($root);
    }

    /**
     * Convierte una ruta relativa a absoluta.
     */
    private function resolvePath(string $path): string
    {
        $trimmed = trim($path, "\"' ");
        $isAbsolute = preg_match('/^[a-zA-Z]:\\\\|^\\\\|^\//', $trimmed) === 1;
        return $isAbsolute ? $trimmed : base_path($trimmed);
    }
}