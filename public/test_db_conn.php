<?php
header('Content-Type: text/plain');
$host = '192.168.1.6';
$port = 1433;
echo "TCP: ";
$fp = @fsockopen($host, $port, $e, $s, 10);
echo $fp ? "OK\n" : "FAIL ($s)\n";
if ($fp) fclose($fp);
echo "PDO: ";
try {
    $p = new PDO(
        "sqlsrv:Server=$host,$port;Database=Whatsapp;Encrypt=optional;TrustServerCertificate=true;LoginTimeout=30",
        'Php', '$89%3a7'
    );
    echo "OK - " . $p->query('SELECT 1')->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "FAIL - " . $e->getMessage() . "\n";
}
echo "Laravel login_timeout: " . config('database.connections.sqlsrv.login_timeout', 'n/a') . "\n";
