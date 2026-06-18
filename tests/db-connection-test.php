<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$admin = DB::selectOne("SELECT id, name, email, role FROM users WHERE email = 'admin@erp.com'");
$agent = DB::selectOne("SELECT id, name, email, role FROM users WHERE email = 'ana@erp.com'");

if (!$admin || !$agent) {
    echo "FALHA: usuarios nao encontrados\n";
    exit(1);
}

echo "OK: admin={$admin->email} agent={$agent->email}\n";
echo "contacts=" . DB::table('contacts')->count() . " conversations=" . DB::table('conversations')->count() . "\n";

$datetimeCols = DB::selectOne("SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE DATA_TYPE = 'datetime' AND TABLE_SCHEMA = 'dbo'");
if ($datetimeCols->c > 0) {
    echo "AVISO: ainda existem {$datetimeCols->c} colunas datetime\n";
    exit(1);
}

echo "OK: todas colunas datetime convertidas para datetime2\n";
