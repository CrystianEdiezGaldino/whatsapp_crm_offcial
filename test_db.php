<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Teste de Conectividade SQL Server</h2>";
echo "<pre>";

$host = '192.168.1.6';
$port = 1433;
$database = 'Whatsapp';
$username = 'Php';
$password = '$89%3a7';

echo "Configuração:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: [HIDDEN]\n\n";

$tcpOk = false;
$pdoOk = false;

// 1. TCP
echo "=== 1. Teste de conectividade TCP ===\n";
$connection_attempt = @fsockopen($host, $port, $errno, $errstr, 5);
if ($connection_attempt) {
    $tcpOk = true;
    echo "✓ TCP connection successful to $host:$port\n";
    fclose($connection_attempt);
} else {
    echo "✗ TCP connection failed: $errstr (Code: $errno)\n";
}

// 2. Drivers (só extensões PHP — NÃO significa banco acessível)
echo "\n=== 2. Verificar ODBC Driver (extensões PHP) ===\n";
echo "Drivers ODBC instalados:\n";
echo shell_exec('odbcinst -q -d 2>&1') ?: "Nenhum driver encontrado\n";
echo "Extensões PHP: sqlsrv=" . (extension_loaded('sqlsrv') ? 'OK' : 'FAIL');
echo ", pdo_sqlsrv=" . (extension_loaded('pdo_sqlsrv') ? 'OK' : 'FAIL') . "\n";
echo ">>> Drivers OK = PHP preparado. Conexão real depende do teste 1 (TCP).\n";

// 3. PDO
echo "\n=== 3. Teste PDO Connection ===\n";
try {
    $dsn = "sqlsrv:Server=$host,$port;Database=$database;LoginTimeout=5";
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdoOk = true;
    echo "✓ PDO Connection successful!\n";
    echo "✓ Query: " . $pdo->query('SELECT 1')->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "✗ PDO Connection failed: " . $e->getMessage() . "\n";
}

// 4. PDO com encryption
echo "\n=== 4. Teste com Encryption Options ===\n";
try {
    $dsn = "sqlsrv:Server=$host,$port;Database=$database;Encrypt=optional;TrustServerCertificate=true;LoginTimeout=5";
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✓ Connection with Encrypt=optional successful!\n";
    echo "✓ Query: " . $pdo->query('SELECT 1')->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
}

// 5. Laravel
echo "\n=== 5. Teste Laravel Connection ===\n";
try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $result = \Illuminate\Support\Facades\DB::connection('sqlsrv')->select('SELECT 1 as test');
    echo "✓ Laravel connection successful!\n";
    print_r($result);
} catch (Throwable $e) {
    echo "✗ Laravel connection failed: " . $e->getMessage() . "\n";
}

// Resumo
echo "\n=== RESUMO ===\n";
if ($tcpOk && $pdoOk) {
    echo "✓ BANCO ACESSÍVEL — login deve funcionar.\n";
} else {
    echo "✗ BANCO INACESSÍVEL — mesmo erro do login.\n";
    echo "  Servidor web (192.168.255.5) não alcança SQL ($host:$port).\n";
    echo "  Ação: liberar rede no roteador 192.168.255.2 + firewall SQL Server.\n";
}

echo "</pre>";
