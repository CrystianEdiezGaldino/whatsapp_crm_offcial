<?php
// Teste rápido do LoginController::databaseReachable
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$host = config('database.connections.sqlsrv.host');
$port = config('database.connections.sqlsrv.port');
$fp = @fsockopen($host, $port, $e, $s, 5);
echo $fp ? "PASS: $host:$port acessivel\n" : "FAIL: $host:$port ($s)\n";
if ($fp) fclose($fp);
