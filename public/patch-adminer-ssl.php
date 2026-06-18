<?php
/**
 * Aplica TrustServerCertificate no Adminer (ODBC Driver 18 + cert autoassinado).
 * Uso: sudo php patch-adminer-ssl.php
 */
$file = __DIR__ . '/adminer-core.php';
if (!is_file($file)) {
    fwrite(STDERR, "adminer-core.php não encontrado\n");
    exit(1);
}

$content = file_get_contents($file);
$old = '$_b=array("UID"=>$V,"PWD"=>$F,"CharacterSet"=>"UTF-8");';
$new = '$_b=array("UID"=>$V,"PWD"=>$F,"CharacterSet"=>"UTF-8","TrustServerCertificate"=>true,"Encrypt"=>true);';

if (strpos($content, 'TrustServerCertificate') !== false) {
    echo "OK: já aplicado\n";
    exit(0);
}

if (strpos($content, $old) === false) {
    fwrite(STDERR, "Padrão não encontrado no adminer-core.php\n");
    exit(1);
}

file_put_contents($file, str_replace($old, $new, $content));
echo "OK: patch aplicado\n";
