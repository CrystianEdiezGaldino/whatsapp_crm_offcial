<?php
header('Content-Type: text/plain; charset=utf-8');
$server = '127.0.0.1';
$user = 'Php';
$pass = '$89%3a7';
$db = 'Whatsapp';

echo 'sqlsrv: ' . (extension_loaded('sqlsrv') ? 'yes' : 'no') . "\n";
echo 'pdo_sqlsrv: ' . (extension_loaded('pdo_sqlsrv') ? 'yes' : 'no') . "\n";

if (extension_loaded('sqlsrv')) {
    $link = @sqlsrv_connect($server, [
        'Database' => $db,
        'UID' => $user,
        'PWD' => $pass,
        'CharacterSet' => 'UTF-8',
        'TrustServerCertificate' => true,
        'Encrypt' => true,
    ]);
    if ($link) {
        $row = sqlsrv_fetch_array(sqlsrv_query($link, 'SELECT DB_NAME() AS db'), SQLSRV_FETCH_ASSOC);
        echo 'sqlsrv_connect: OK ' . ($row['db'] ?? '') . "\n";
    } else {
        echo "sqlsrv_connect: FAIL\n";
        print_r(sqlsrv_errors());
    }
}
