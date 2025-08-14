<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Exporta archivos PEM (clave privada sin contraseña y certificado X.509) desde el .p12 configurado en .env
Artisan::command('sri:export-pem', function () {
    $basePath = base_path();
    $relP12 = env('SRI_CERTIFICADO_ARCHIVO');
    $pass = (string) env('SRI_CERTIFICADO_CLAVE', '');
    $relKey = env('SRI_CERTIFICADO_PEM_KEY', 'storage/certificates/cert.key.pem');
    $relCrt = env('SRI_CERTIFICADO_PEM_CERT', 'storage/certificates/cert.crt.pem');

    if (!$relP12) {
        $this->error('SRI_CERTIFICADO_ARCHIVO no está definido en .env');
        return 1;
    }

    $p12Path = str_starts_with($relP12, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\|\//', $relP12)
        ? $relP12
        : base_path($relP12);
    $keyPath = str_starts_with($relKey, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\|\//', $relKey)
        ? $relKey
        : base_path($relKey);
    $crtPath = str_starts_with($relCrt, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\|\//', $relCrt)
        ? $relCrt
        : base_path($relCrt);

    if (!File::exists($p12Path)) {
        $this->error("No se encontró el archivo P12 en: {$p12Path}");
        return 1;
    }

    // Crear carpetas destino si no existen
    File::ensureDirectoryExists(dirname($keyPath));
    File::ensureDirectoryExists(dirname($crtPath));

    $p12Content = File::get($p12Path);
    $certs = [];
    $ok = false;
    $opensslErrors = [];
    while ($err = openssl_error_string()) { /* limpia buffer de errores previos */ }
    if (function_exists('openssl_pkcs12_read')) {
        $ok = @openssl_pkcs12_read($p12Content, $certs, $pass ?: '');
        if (!$ok) {
            // Capturar errores si existen
            while ($err = openssl_error_string()) { $opensslErrors[] = $err; }
        }
    }

    if ($ok && isset($certs['pkey'], $certs['cert'])) {
        $privateKey = $certs['pkey'];
        $certificate = $certs['cert'];
        // Re-exporta la clave privada sin contraseña (PKCS#8) por consistencia
        $exportedKey = '';
        if (!@openssl_pkey_export($privateKey, $exportedKey, null)) {
            // Si falla la exportación, usa la original
            $exportedKey = $privateKey;
        }
        File::put($keyPath, $exportedKey);
        File::put($crtPath, $certificate);
        // Ajusta permisos en *nix (ignorar errores en Windows)
        @chmod($keyPath, 0600);
        @chmod($crtPath, 0644);

        $this->info('PEM exportados correctamente:');
        $this->line("  Clave: {$keyPath}");
        $this->line("  Cert:  {$crtPath}");
        return 0;
    }

    // Fallback: intentar con comandos openssl si están disponibles
    $this->warn('openssl_pkcs12_read falló, intentando con el binario openssl...');
    if (!function_exists('proc_open')) {
        $this->error('No se pudo usar openssl (proc_open deshabilitado). Errores OpenSSL: ' . implode(' | ', $opensslErrors));
        return 1;
    }
    $passArg = $pass !== '' ? ('-passin pass:' . str_replace(['"', "'"], '', $pass)) : '-passin pass:'; // pass vacío permitido

    $p12Arg = escapeshellarg($p12Path);
    $keyArg = escapeshellarg($keyPath);
    $crtArg = escapeshellarg($crtPath);

    // Extraer clave privada sin cifrar (-nodes)
    $cmdKey = "openssl pkcs12 -in {$p12Arg} -nocerts -nodes {$passArg} -out {$keyArg}";
    // Extraer certificado
    $cmdCrt = "openssl pkcs12 -in {$p12Arg} -nokeys -clcerts {$passArg} -out {$crtArg}";

    $run = function (string $cmd) use ($basePath) {
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($cmd, $descriptorspec, $pipes, $basePath);
        if (!is_resource($process)) {
            return [1, '', 'No se pudo crear el proceso'];
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($process);
        return [$status, $stdout, $stderr];
    };

    [$s1, $o1, $e1] = $run($cmdKey);
    [$s2, $o2, $e2] = $run($cmdCrt);

    if ($s1 === 0 && $s2 === 0 && File::exists($keyPath) && File::exists($crtPath)) {
        @chmod($keyPath, 0600);
        @chmod($crtPath, 0644);
        $this->info('PEM exportados con openssl:');
        $this->line("  Clave: {$keyPath}");
        $this->line("  Cert:  {$crtPath}");
        return 0;
    }

    $this->error('Falló la exportación de PEM.');
    if ($opensslErrors) {
        $this->line('Errores OpenSSL PHP: ' . implode(' | ', $opensslErrors));
    }
    $this->line("Salida openssl key [{$s1}]: {$e1}");
    $this->line("Salida openssl crt [{$s2}]: {$e2}");
    return 1;
})->purpose('Exporta cert.key.pem y cert.crt.pem desde el P12 configurado en .env para uso con SRI');
