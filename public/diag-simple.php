<?php
header('Content-Type: application/json');

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => gethostname(),
];

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = 1433;

$socket = @fsockopen($host, $port, $errno, $errstr, 5);
$result['tcp'] = $socket ? 'OK' : 'FAIL';
$result['tcp_error'] = $socket ? null : "$errstr ($errno)";
if ($socket) {
    fclose($socket);
}

$result['extensions'] = [
    'sqlsrv' => extension_loaded('sqlsrv') ? 'YES' : 'NO',
    'pdo_sqlsrv' => extension_loaded('pdo_sqlsrv') ? 'YES' : 'NO',
];

if (!$socket) {
    $result['laravel_db'] = 'SKIPPED';
    $result['laravel_db_error'] = 'TCP falhou — rede bloqueada';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $result['env'] = [
        'DB_HOST' => config('database.connections.sqlsrv.host'),
        'DB_PORT' => config('database.connections.sqlsrv.port'),
        'DB_DATABASE' => config('database.connections.sqlsrv.database'),
        'DB_USERNAME' => config('database.connections.sqlsrv.username'),
    ];

    $start = microtime(true);
    \Illuminate\Support\Facades\DB::connection('sqlsrv')->select('SELECT 1 as ok');
    $result['laravel_db'] = 'CONNECTED';
    $result['laravel_db_time_ms'] = round((microtime(true) - $start) * 1000);
} catch (Throwable $e) {
    $result['laravel_db'] = 'FAILED';
    $result['laravel_db_error'] = $e->getMessage();
    $result['laravel_db_time_ms'] = isset($start) ? round((microtime(true) - $start) * 1000) : 0;
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
