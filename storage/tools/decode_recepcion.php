<?php
// Decode base64 <xml>...</xml> content from a recepcion_request_*.xml and write decoded XML alongside it
if ($argc < 2) {
    fwrite(STDERR, "Usage: php decode_recepcion.php <recepcion_request.xml>\n");
    exit(1);
}
$in = $argv[1];
$raw = file_get_contents($in);
if ($raw === false) {
    fwrite(STDERR, "Cannot read: $in\n");
    exit(1);
}
if (!preg_match('#<xml>([\s\S]*?)</xml>#', $raw, $m)) {
    fwrite(STDERR, "No <xml>...</xml> block found.\n");
    exit(1);
}
$b64 = trim($m[1]);
$data = base64_decode($b64, true);
if ($data === false) {
    fwrite(STDERR, "Base64 decode failed.\n");
    exit(1);
}
$out = preg_replace('#(\.xml)$#', '_decoded.xml', $in);
if ($out === $in) {
    $out .= '.decoded.xml';
}
file_put_contents($out, $data);
echo "Decoded to: $out\n";
$snippet = substr($data, 0, 800);
echo $snippet, "\n";
