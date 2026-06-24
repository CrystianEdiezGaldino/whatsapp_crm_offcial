<?php

/**
 * Teste: conversa vazia não entra na fila; dedupe prefere conversa com mensagens.
 * Uso: php tests/queue-empty-stub-test.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Conversation;
use App\Models\Message;

$errors = 0;

function assertTrue(bool $cond, string $msg): void
{
    global $errors;
    if (!$cond) {
        echo "FAIL: {$msg}\n";
        $errors++;
    } else {
        echo "OK: {$msg}\n";
    }
}

$empty = new Conversation(['status' => 'new', 'claimed_by' => null]);
$empty->setRelation('activeClaim', null);
$empty->setRelation('lastMessage', null);
assertTrue(!$empty->isPendingInQueue(), 'conversa vazia não é pendente');

$withMsg = new Conversation(['status' => 'new', 'claimed_by' => null]);
$withMsg->setRelation('activeClaim', null);
$withMsg->setRelation('lastMessage', new Message(['content' => 'teste']));
assertTrue($withMsg->isPendingInQueue(), 'conversa com mensagem é pendente');

exit($errors > 0 ? 1 : 0);
