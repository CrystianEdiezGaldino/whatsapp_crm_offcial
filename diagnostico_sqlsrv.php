<?php
/**
 * Diagnóstico SQL Server — mesmo padrão do projeto Agenda de Salão (adaptado Linux)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO SQL SERVER ===\n\n";

// Carrega .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("ERRO: .env não encontrado\n");
}
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
        continue;
    }
    [$k, $v] = explode('=', $line, 2);
    putenv(trim($k) . '=' . trim($v));
}

$host = getenv('DB_HOST') ?: '192.168.1.6';
$port = getenv('DB_PORT') ?: '1433';
$db   = getenv('DB_DATABASE') ?: '';
$user = getenv('DB_USERNAME') ?: '';
$pass = getenv('DB_PASSWORD') ?: '';

echo "1. Sistema\n";
echo "   PHP: " . PHP_VERSION . "\n";
echo "   OS: " . PHP_OS . "\n";
echo "   sqlsrv: " . (extension_loaded('sqlsrv') ? 'OK' : 'FALTA') . "\n";
echo "   pdo_sqlsrv: " . (extension_loaded('pdo_sqlsrv') ? 'OK' : 'FALTA') . "\n";
echo "   ODBC: " . trim(shell_exec('odbcinst -q -d 2>&1') ?: 'n/a') . "\n\n";

echo "2. .env\n";
echo "   DB_HOST=$host\n";
echo "   DB_PORT=$port\n";
echo "   DB_DATABASE=$db\n";
echo "   DB_USERNAME=$user\n";
echo "   DB_TRUST_SERVER_CERTIFICATE=" . (getenv('DB_TRUST_SERVER_CERTIFICATE') ?: 'n/a') . "\n\n";

echo "3. Rede TCP ($host:$port)\n";
$fp = @fsockopen($host, $port, $errno, $errstr, 5);
if ($fp) {
    fclose($fp);
    echo "   OK - porta acessível\n\n";
} else {
    echo "   FALHA - $errstr ($errno)\n";
    echo "   >>> Outro projeto (WAMP/Windows) funciona porque está na rede 192.168.1.x\n";
    echo "   >>> Este servidor (192.168.255.5) precisa de rota + firewall liberados\n\n";
}

echo "4. PDO (trust_server_certificate)\n";
try {
    $dsn = "sqlsrv:Server=$host,$port;Database=$db;Encrypt=yes;TrustServerCertificate=true;LoginTimeout=10";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "   OK - SELECT 1 = " . $pdo->query('SELECT 1')->fetchColumn() . "\n";
} catch (Throwable $e) {
    echo "   FALHA - " . $e->getMessage() . "\n";
}

echo "\n=== FIM ===\n";
