<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$host = config('database.connections.sqlsrv.host');
$port = config('database.connections.sqlsrv.port');
$db   = config('database.connections.sqlsrv.database');

echo "Laravel DB: $host:$port / $db\n";

$fp = @fsockopen($host, $port, $e, $s, 5);
echo $fp ? "TCP: OK\n" : "TCP: FAIL ($s)\n";
if ($fp) fclose($fp);

try {
    $r = \Illuminate\Support\Facades\DB::select('SELECT 1 as ok');
    echo "Laravel: OK - " . json_encode($r) . "\n";
} catch (Throwable $ex) {
    echo "Laravel: FAIL - " . $ex->getMessage() . "\n";
}
