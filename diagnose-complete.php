<?php
/**
 * Script de diagnóstico completo para conexão SQL Server
 * Coloque em: /var/www/smcc-whatsapp/public/diagnose-complete.php
 * Acesse: http://192.168.255.5/smcc-whatsapp/public/diagnose-complete.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico SQL Server</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
        .ok { color: #4ec9b0; }
        .fail { color: #f48771; }
        .warn { color: #ce9178; }
        pre { background: #252526; padding: 10px; border-left: 3px solid #007acc; overflow-x: auto; }
        section { margin: 20px 0; border-bottom: 1px solid #444; padding-bottom: 15px; }
        h2 { color: #569cd6; }
    </style>
</head>
<body>

<h1>🔍 Diagnóstico SQL Server Connection</h1>
<p>Servidor: <?php echo gethostname(); ?> (<?php echo gethostbyname(gethostname()); ?>)</p>

<?php
// Config
$configs = [
    'Original (config)' => ['192.168.1.6', 1433],
    'Localhost' => ['127.0.0.1', 1433],
    'Current Host' => [gethostbyname(gethostname()), 1433],
];

?>

<section>
<h2>1️⃣ Teste de Conectividade TCP (fsockopen)</h2>
<pre><?php
foreach ($configs as $name => $config) {
    list($host, $port) = $config;
    $socket = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($socket) {
        echo "<span class='ok'>✅ $name ($host:$port) - ABERTO</span>\n";
        fclose($socket);
    } else {
        echo "<span class='fail'>❌ $name ($host:$port) - FECHADO ($errstr)</span>\n";
    }
}
?></pre>
</section>

<section>
<h2>2️⃣ Teste de DNS/IP</h2>
<pre><?php
echo "IP do servidor atual: " . (gethostbyname(gethostname()) ?: 'não resolveu') . "\n";
echo "Hostname: " . gethostname() . "\n";
echo "192.168.1.6 resolve para: " . (gethostbyname('192.168.1.6') ?: 'não resolveu') . "\n";
echo "Hostname 'servicos2.santamonica.rec.br' resolve para: " . (gethostbyname('servicos2.santamonica.rec.br') ?: 'não resolveu') . "\n";
?></pre>
</section>

<section>
<h2>3️⃣ Extensões PHP Necessárias</h2>
<pre><?php
$extensions = ['sqlsrv', 'pdo', 'pdo_sqlsrv', 'odbc'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='ok'>✅ $ext - INSTALADO</span>\n";
    } else {
        echo "<span class='fail'>❌ $ext - FALTANDO</span>\n";
    }
}
?></pre>
</section>

<section>
<h2>4️⃣ Teste PDO Connection com Diferentes DSNs</h2>
<pre><?php

$user = 'Php';
$pass = '$89%3a7';
$db = 'Whatsapp';

$dsns = [
    'sqlsrv:Server=192.168.1.6,1433;Database=Whatsapp' => 'Original IP:PORT',
    'sqlsrv:Server=192.168.1.6;Database=Whatsapp' => 'Original IP (sem porta)',
    'sqlsrv:Server=127.0.0.1,1433;Database=Whatsapp' => 'Localhost',
];

foreach ($dsns as $dsn => $desc) {
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);

        $db_name = $pdo->query("SELECT DB_NAME() AS db")->fetchColumn();
        echo "<span class='ok'>✅ $desc - CONECTADO</span>\n";
        echo "   Banco: $db_name\n";

    } catch (PDOException $e) {
        echo "<span class='fail'>❌ $desc</span>\n";
        echo "   Erro: " . substr($e->getMessage(), 0, 100) . "\n";
    }
}

?></pre>
</section>

<section>
<h2>5️⃣ Teste Laravel Database</h2>
<pre><?php

if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
    try {
        require __DIR__ . '/../bootstrap/app.php';

        $pdo = DB::connection('sqlsrv')->getPdo();
        $version = $pdo->query("SELECT @@VERSION AS v")->fetchColumn();
        $db_name = $pdo->query("SELECT DB_NAME() AS db")->fetchColumn();

        echo "<span class='ok'>✅ Laravel Database Connection FUNCIONANDO</span>\n";
        echo "Banco: $db_name\n";
        echo "Versão: " . substr($version, 0, 60) . "\n";

    } catch (Exception $e) {
        echo "<span class='fail'>❌ Laravel Database Connection FALHOU</span>\n";
        echo "Erro: " . $e->getMessage() . "\n";
    }
} else {
    echo "<span class='warn'>⚠️  Bootstrap não encontrado</span>\n";
}

?></pre>
</section>

<section>
<h2>6️⃣ Variáveis de Ambiente Lidas</h2>
<pre><?php
require __DIR__ . '/../bootstrap/app.php';
$cfg = config('database.connections.sqlsrv');

echo "DB_CONNECTION: " . env('DB_CONNECTION') . "\n";
echo "DB_HOST: " . env('DB_HOST') . "\n";
echo "DB_PORT: " . env('DB_PORT') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE') . "\n";
echo "DB_USERNAME: " . env('DB_USERNAME') . "\n";
echo "DB_PASSWORD (lido): " . env('DB_PASSWORD') . "\n";
echo "DB_ENCRYPT: " . env('DB_ENCRYPT') . "\n";
echo "DB_TRUST_SERVER_CERTIFICATE: " . env('DB_TRUST_SERVER_CERTIFICATE') . "\n";
?></pre>
</section>

<hr style="border: 1px solid #444; margin: 30px 0;">

<h2>📋 Próximos Passos:</h2>
<ol>
    <li>Se todas as conexões PDO falharem: <strong>problema de REDE/FIREWALL</strong> entre servidores</li>
    <li>Se localhost:1433 funcionar: SQL Server está no mesmo servidor (mudar DB_HOST para 127.0.0.1)</li>
    <li>Se nenhum funcionar: SQL Server não está rodando ou em porta diferente</li>
</ol>

</body>
</html>
