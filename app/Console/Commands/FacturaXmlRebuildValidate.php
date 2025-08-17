<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FacturaElectronicaService;
use App\Services\SRI\XMLSigner;

class FacturaXmlRebuildValidate extends Command
{
    protected $signature = 'sri:factura:rebuild-validate {id : ID de la factura_electronica} {--xsd= : Ruta de XSD (opcional)}';
    protected $description = 'Regenera, firma y valida el XML de una factura electrónica contra los XSD locales';

    public function handle(FacturaElectronicaService $service)
    {
        $id = (int) $this->argument('id');
        $xsdOpt = $this->option('xsd');
        try {
            $this->info("Regenerando XML de la factura {$id}...");
            $factura = $service->regenerarXMLDocumento($id);
            $xml = $factura->xml_documento;
            if (!$xml) {
                $this->error('No se pudo generar el XML del documento.');
                return 1;
            }

            $this->info('Firmando XML...');
            $signer = new XMLSigner();
            $xmlFirmado = $signer->sign($xml);

            $signedDir = storage_path('app/sri/signed');
            if (!is_dir($signedDir)) @mkdir($signedDir, 0755, true);
            $signedPath = $signedDir . DIRECTORY_SEPARATOR . 'factura_' . $id . '_firmada.xml';
            file_put_contents($signedPath, $xmlFirmado);
            $this->info("XML firmado guardado en: {$signedPath}");

            $validator = base_path('storage/XSD/validar_xml_sri.php');
            if (!file_exists($validator)) {
                $this->warn('Validador no encontrado en storage/XSD/validar_xml_sri.php. Solo se firmó.');
                return 0;
            }

            $this->info('Validando contra XSD...');
            $cmd = 'php ' . escapeshellarg($validator) . ' ' . escapeshellarg($signedPath);
            if ($xsdOpt) {
                $cmd .= ' ' . escapeshellarg($xsdOpt);
            }
            $this->line("> {$cmd}");
            $descriptor = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $proc = proc_open($cmd, $descriptor, $pipes, base_path());
            if (is_resource($proc)) {
                $out = stream_get_contents($pipes[1]);
                $err = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $exit = proc_close($proc);
                if ($out) $this->info(trim($out));
                if ($err) $this->error(trim($err));
                return $exit;
            } else {
                $this->error('No se pudo ejecutar el validador.');
                return 1;
            }
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
