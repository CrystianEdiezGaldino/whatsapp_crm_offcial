<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ref = new ReflectionClass(App\Services\WhatsAppService::class);
$method = $ref->getMethod('resolveSenderName');
$method->setAccessible(true);
$wa = new App\Services\WhatsAppService();

// Sem usuário logado (fluxo/bot)
$botName = $method->invoke($wa);
echo "Sem auth: [{$botName}]\n";
echo ($botName === 'Assistente Virtual' || $botName !== 'Agente' ? "OK: nao usa 'Agente' como padrao\n" : "FALHA: ainda usa Agente\n");

// Com usuário simulado
$user = App\Models\User::query()->first();
if ($user) {
    auth()->login($user);
    $agentName = $method->invoke($wa);
    echo "Com auth ({$user->name}): [{$agentName}]\n";
    echo ($agentName === $user->name ? "OK: usa nome do agente logado\n" : "FALHA\n");
} else {
    echo "SKIP: sem usuario no banco para testar auth\n";
}
