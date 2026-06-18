<?php
/**
 * Teste: pollList retorna JSON com conversas.
 * Uso: php tests/chat-list-poll-test.php
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$route = route('conversations.list-poll');
$ok = str_contains($route, 'list-poll')
    && class_exists(\App\Http\Controllers\ConversationController::class)
    && method_exists(\App\Http\Controllers\ConversationController::class, 'pollList');

echo ($ok ? 'PASS' : 'FAIL') . ": route=$route\n";
exit($ok ? 0 : 1);
