<?php
if ($argc < 2) { fwrite(STDERR, "Usage: php b64decode_file.php <in> [out]\n"); exit(1);} 
$in=$argv[1]; $out = $argc>=3 ? $argv[2] : preg_replace('#(\.[^\.]+)$#','_decoded$1',$in);
$data=file_get_contents($in); if($data===false){fwrite(STDERR,"Cannot read $in\n"); exit(1);} 
$data=trim($data); $bin=base64_decode($data,true); if($bin===false){fwrite(STDERR,"base64_decode failed\n"); exit(1);} file_put_contents($out,$bin); echo "Wrote $out\n"; $snippet=substr($bin,0,200); echo $snippet, "\n";
