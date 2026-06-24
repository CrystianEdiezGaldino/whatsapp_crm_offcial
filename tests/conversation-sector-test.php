<?php

/**
 * Teste: setor no atendimento (model + rotas + UI).
 * Uso: php tests/conversation-sector-test.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Sector;

$errors = 0;

function ok(bool $cond, string $msg): void
{
    global $errors;
    if (!$cond) {
        echo "FAIL: {$msg}\n";
        $errors++;
    } else {
        echo "OK: {$msg}\n";
    }
}

$sector = new Sector(['id' => 3, 'name' => 'Financeiro']);
$ui = $sector->toUiArray();
ok($ui['name'] === 'Financeiro', 'nome do setor');
ok($ui['color'] !== '', 'cor do setor');

$default = Sector::defaultUi();
ok($default['name'] === 'Geral', 'setor padrão');

$blade = file_get_contents(__DIR__ . '/../resources/views/components/chat/contact-panel.blade.php');
ok(str_contains($blade, 'openSectorModalBtn'), 'painel tem botão de setor');
ok(str_contains($blade, 'sectorModal'), 'painel tem modal de setor');

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
ok(str_contains($routes, 'conversations.sector.update'), 'rota de update de setor');

exit($errors > 0 ? 1 : 0);
