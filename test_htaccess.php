<?php
// Teste: raiz deve reescrever para public/login (ver .htaccess)
header('Content-Type: text/plain; charset=utf-8');

$rootHtaccess = file_get_contents(__DIR__ . '/.htaccess');
$publicHtaccess = file_get_contents(__DIR__ . '/public/.htaccess');

$checks = [
    'root_rewrite_login' => str_contains($rootHtaccess, 'RewriteRule ^$ public/login'),
    'root_rewrite_base' => str_contains($rootHtaccess, 'RewriteBase /smcc-whatsapp/'),
    'public_rewrite_base' => str_contains($publicHtaccess, 'RewriteBase /smcc-whatsapp/public/'),
];

$all = !in_array(false, $checks, true);
foreach ($checks as $name => $ok) {
    echo ($ok ? 'PASS' : 'FAIL') . ": $name\n";
}
echo $all ? "\nOK: htaccess configurado\n" : "\nERRO: revisar htaccess\n";
