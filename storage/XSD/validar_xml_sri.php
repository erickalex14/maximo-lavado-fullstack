<?php
/**
 * Validator for SRI XML invoices using local XSDs.
 * Usage:
 *   php storage/XSD/validar_xml_sri.php <xml_file> [<schema_dir>]
 *
 * Features:
 * - Auto-discovers XSDs recursively in storage/XSD and optional <schema_dir>.
 * - Picks the most relevant XSD for the root element (e.g., factura) and version (1.0.0 / 1.1.0).
 * - Validates with libxml and prints readable error messages with file/line/column.
 * - Adds heuristic checks for common SRI issues when XSDs are incomplete:
 *   - Missing <tarifa> in totalConImpuestos/totalImpuesto and detalles/detalle/impuestos/impuesto.
 *   - Non-standard CanonicalizationMethod (suggests c14n 20010315 over exclusive c14n when needed).
 */

ini_set('display_errors', 'stderr');
libxml_use_internal_errors(true);

function usage($code = 2) {
    fwrite(STDERR, "Uso: php storage/XSD/validar_xml_sri.php <xml_file> [<schema_dir>]" . PHP_EOL);
    exit($code);
}

if ($argc < 2) {
    usage(2);
}

$xmlPath = $argv[1];
$overrideSchemaDir = $argv[2] ?? null;

if (!is_file($xmlPath)) {
    fwrite(STDERR, "No existe el XML: $xmlPath" . PHP_EOL);
    exit(2);
}

$baseDir = __DIR__;
$searchDirs = [];
// If a second arg is provided and is an XSD file, try only that file first.
$overrideSchemaFile = null;
if ($overrideSchemaDir) {
    $realOverride = realpath($overrideSchemaDir);
    if ($realOverride && is_file($realOverride) && preg_match('/\.xsd$/i', $realOverride)) {
        $overrideSchemaFile = $realOverride;
    } elseif ($realOverride && is_dir($realOverride)) {
        $searchDirs[] = $realOverride;
    }
}

// Always prioritize the bundled 'XML y XSD Factura' folder if present
$preferredDir = $baseDir . DIRECTORY_SEPARATOR . 'XML y XSD Factura';
if (is_dir($preferredDir)) {
    $searchDirs[] = realpath($preferredDir);
}

$searchDirs[] = $baseDir;
// Common subfolders where users might add official schemas
$likelySubdirs = [
    'SRI', 'schemas', 'schema', 'xsd', 'XSD', 'factura', 'facturas', 'invoice',
    'comprobantes', 'comprobantes-electronicos', 'v1.1.0', '1.1.0', 'v1.0.0', '1.0.0'
];
foreach ($likelySubdirs as $sub) {
    $p = $baseDir . DIRECTORY_SEPARATOR . $sub;
    if (is_dir($p)) $searchDirs[] = $p;
}

// Recursively list available XSD files
function list_xsd_files(array $dirs): array {
    $files = [];
    $seen = [];
    foreach ($dirs as $dir) {
        $dir = realpath($dir);
        if (!$dir || isset($seen[$dir])) continue;
        $seen[$dir] = true;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && preg_match('/\\.xsd$/i', $f->getFilename())) {
                $files[] = $f->getPathname();
            }
        }
    }
    return array_values(array_unique($files));
}

// Try to pick best XSD candidate
function pick_xsd_for(string $rootName, ?string $version, array $xsdFiles): array {
    // Score files by filename patterns and version match
    $candidates = [];
    $ver = $version ? strtolower($version) : '';
    $verCollapsed = $ver ? str_replace(['.', '-'], ['', ''], $ver) : '';
    $verUnderscore = $ver ? str_replace('.', '_', $ver) : '';

    foreach ($xsdFiles as $xsd) {
        $file = strtolower(basename($xsd));
        $score = 0;
        // Strongly prefer factura-specific schemas when root is factura
        if ($rootName === 'factura' && str_starts_with($file, 'factura')) $score += 50;
        if (strpos($file, $rootName) !== false) $score += 15; // match root name
        if (strpos($file, 'factura') !== false) $score += 8;
        if (strpos($file, 'comprobante') !== false) $score += 3; // fallback general schema
        if ($ver && strpos($file, $ver) !== false) $score += 10;
        if ($verCollapsed && strpos($file, $verCollapsed) !== false) $score += 8;
        if ($verUnderscore && strpos($file, $verUnderscore) !== false) $score += 8;
        if (strpos($file, '2.1') !== false && $ver === '2.1.0') $score += 6;
        if (strpos($file, '2.0') !== false && $ver === '2.0.0') $score += 6;
        if (strpos($file, '1.1') !== false && $ver === '1.1.0') $score += 6;
        if (strpos($file, '1.0') !== false && $ver === '1.0.0') $score += 6;
        if (strpos($file, 'offline') !== false) $score += 2;
        if ($score > 0) {
            $candidates[] = ['xsd' => $xsd, 'score' => $score];
        }
    }

    // Sort by score desc, then shortest path (heuristic: specific folders first)
    usort($candidates, function ($a, $b) {
        if ($a['score'] === $b['score']) return strlen($a['xsd']) <=> strlen($b['xsd']);
        return $b['score'] <=> $a['score'];
    });

    // Return up to 12 best candidates to try
    return array_slice(array_map(fn($c) => $c['xsd'], $candidates), 0, 12);
}

// Load XML
$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->formatOutput = false;
if (!$doc->load($xmlPath)) {
    fwrite(STDERR, "No se pudo cargar el XML: $xmlPath" . PHP_EOL);
    foreach (libxml_get_errors() as $err) {
        fwrite(STDERR, trim($err->message) . " en línea {$err->line}, columna {$err->column}" . PHP_EOL);
    }
    exit(2);
}

$rootName = $doc->documentElement->localName;
$version = $doc->documentElement->getAttribute('version') ?: null;

$xsdFiles = list_xsd_files($searchDirs);
// If a specific .xsd file was provided, try that first
if ($overrideSchemaFile) {
    array_unshift($xsdFiles, $overrideSchemaFile);
}

// Prepare include directories for resolving imported XSDs
$includeDirs = array_values(
    array_unique(
        array_filter(
            array_map(function ($p) {
                return is_dir($p) ? realpath($p) : (is_file($p) ? realpath(dirname($p)) : null);
            }, array_merge($searchDirs, $xsdFiles))
        )
    )
);

// Register external entity loader to resolve imported XSDs from includeDirs
if (function_exists('libxml_set_external_entity_loader')) {
    libxml_set_external_entity_loader(function ($public, $system, $context) use ($includeDirs, $baseDir) {
        // If absolute path exists, open directly
        $candidates = [];
        if ($system) {
            // Normalize file:// URIs
            $sys = preg_replace('#^file://#i', '', $system);
            if (preg_match('/^[a-zA-Z]:\\\\|^\\\\\\\\|^\//', $sys) && file_exists($sys)) {
                $candidates[] = $sys;
            }
            // Special-case common SRI filename: prefer our bundled complete xmldsig schema
            $special = $baseDir . DIRECTORY_SEPARATOR . 'xmldsig-core-schema.xsd';
            if (stripos($sys, 'xmldsig-core-schema.xsd') !== false && file_exists($special)) {
                // Put our known-good schema first
                $candidates[] = $special;
            }
            // Try relative to include dirs
            foreach ($includeDirs as $dir) {
                $p = $dir . DIRECTORY_SEPARATOR . $sys;
                if (file_exists($p)) $candidates[] = $p;
            }
        }
        foreach ($candidates as $cand) {
            $h = @fopen($cand, 'rb');
            if ($h) return $h;
        }
        // Fallback to default resolution
        return null;
    });
}
if (empty($xsdFiles)) {
    fwrite(STDERR, "No se encontraron archivos XSD en: " . implode(', ', $searchDirs) . PHP_EOL);
    exit(2);
}

$candidates = pick_xsd_for($rootName, $version, $xsdFiles);
if (empty($candidates)) {
    // Fall back to all XSDs if scoring fails
    $candidates = $xsdFiles;
}

$errorsByXsd = [];
$validated = false;
$usedXsd = null;

foreach ($candidates as $xsd) {
    $schema = realpath($xsd);
    if (!$schema) continue;
    libxml_clear_errors();
    $cwd = getcwd();
    $schemaDir = dirname($schema);
    // Ensure xmldsig-core-schema.xsd is reachable relative to the schema file
    $xmldsigTarget = $schemaDir . DIRECTORY_SEPARATOR . 'xmldsig-core-schema.xsd';
    $xmldsigSource = $baseDir . DIRECTORY_SEPARATOR . 'xmldsig-core-schema.xsd';
    if (file_exists($xmldsigSource)) {
        // Always overwrite to avoid incomplete stubs in schema folders
        @copy($xmldsigSource, $xmldsigTarget);
    }
    chdir($schemaDir); // so relative imports/includes work
    $ok = $doc->schemaValidate($schema);
    chdir($cwd);
    if ($ok) {
        $validated = true;
        $usedXsd = $schema;
        break;
    } else {
        $errs = libxml_get_errors();
        $formatted = [];
        foreach ($errs as $e) {
            $level = match ($e->level) {
                LIBXML_ERR_WARNING => 'WARNING',
                LIBXML_ERR_ERROR => 'ERROR',
                LIBXML_ERR_FATAL => 'FATAL',
                default => 'INFO',
            };
            $formatted[] = sprintf('%s: %s (línea %d, columna %d)', $level, trim($e->message), $e->line, $e->column);
        }
        $errorsByXsd[$schema] = $formatted;
    }
}

if ($validated) {
    echo "VALIDO ✓ - Schema: $usedXsd" . PHP_EOL;
    echo "Elemento raíz: $rootName, versión: " . ($version ?: 'N/A') . PHP_EOL;
    exit(0);
}

// If we reach here, validation failed on all candidates. Show the most promising error set.
// Choose the XSD with the fewest errors, as it's likely closest to correct.
$bestXsd = null;
$bestErrors = null;
foreach ($errorsByXsd as $xsd => $errs) {
    if ($bestErrors === null || count($errs) < count($bestErrors)) {
        $bestXsd = $xsd;
        $bestErrors = $errs;
    }
}

fwrite(STDERR, "NO VALIDO ✗" . PHP_EOL);
if ($bestXsd) {
    fwrite(STDERR, "Intento más cercano: $bestXsd" . PHP_EOL);
    $limit = 25;
    $shown = 0;
    foreach ($bestErrors as $line) {
        fwrite(STDERR, " - $line" . PHP_EOL);
        if (++$shown >= $limit) break;
    }
}

// Heuristic checks to guide fixes even when schemas are incomplete
$xpath = new DOMXPath($doc);
$xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

$issues = [];
// Missing tarifa in totals
$nodes = $xpath->query('//totalConImpuestos/totalImpuesto[(codigo="2" or codigo="3" or codigo="4") and not(tarifa)]');
if ($nodes && $nodes->length > 0) {
    $issues[] = "Falta <tarifa> en totalConImpuestos/totalImpuesto (IVA). Ej: <tarifa>15.00</tarifa> para 15%.";
}
// Missing tarifa in detail taxes
$nodes = $xpath->query('//detalles/detalle/impuestos/impuesto[(codigo="2" or codigo="3" or codigo="4") and not(tarifa)]');
if ($nodes && $nodes->length > 0) {
    $issues[] = "Falta <tarifa> en detalles/detalle/impuestos/impuesto (IVA). Debe coincidir con codigoPorcentaje.";
}
// Check CanonicalizationMethod
$canon = $xpath->evaluate('string(//ds:SignedInfo/ds:CanonicalizationMethod/@Algorithm)');
if ($canon) {
    $exclusive = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    $c14n = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    if ($canon === $exclusive) {
        $issues[] = "CanonicalizationMethod exclusivo detectado. Sugerido: $c14n (c14n 20010315).";
    }
}
// Version vs schemas present
if ($version) {
    $hasVer = false;
    foreach ($xsdFiles as $x) {
        if (stripos(basename($x), str_replace('.', '', $version)) !== false || stripos(basename($x), $version) !== false) {
            $hasVer = true; break;
        }
    }
    if (!$hasVer) {
        $issues[] = "No se encontró XSD que coincida con versión=$version. Proporcione carpeta con esa versión o use el parámetro <schema_dir>.";
    }
}

if (!empty($issues)) {
    fwrite(STDERR, PHP_EOL . "Sugerencias:" . PHP_EOL);
    foreach ($issues as $s) fwrite(STDERR, " * $s" . PHP_EOL);
}

exit(1);
