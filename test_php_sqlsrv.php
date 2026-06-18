<?php
// Teste rápido das extensões PHP para SQL Server
header('Content-Type: text/plain; charset=utf-8');

$ok = extension_loaded('sqlsrv') && extension_loaded('pdo_sqlsrv');
echo $ok ? "PASS: sqlsrv e pdo_sqlsrv carregados\n" : "FAIL: extensões ausentes\n";
echo "PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "ODBC: " . trim(shell_exec('odbcinst -q -d 2>&1')) . "\n";
