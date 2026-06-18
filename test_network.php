<?php
header('Content-Type: text/plain; charset=utf-8');

$host = '192.168.1.6';
$port = 1433;

echo "=== Teste rede SQL Server ===\n";
echo "Origem: " . (gethostname() ?: 'web') . "\n";
echo "Destino: $host:$port\n\n";

$start = microtime(true);
$fp = @fsockopen($host, $port, $errno, $errstr, 5);
$ms = round((microtime(true) - $start) * 1000);

if ($fp) {
    fclose($fp);
    echo "PASS: TCP conectou em {$ms}ms\n";
} else {
    echo "FAIL: TCP nao conectou em {$ms}ms\n";
    echo "Erro: $errstr ($errno)\n";
    echo "\nAcao necessaria:\n";
    echo "1. Roteador 192.168.255.2: rota para 192.168.1.0/24\n";
    echo "2. SQL Server 192.168.1.6: liberar porta 1433 para 192.168.255.5\n";
}
