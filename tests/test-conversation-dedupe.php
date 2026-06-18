<?php

/**
 * Teste: deduplicação de conversas por contato.
 * Uso: php tests/test-conversation-dedupe.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Conversation;
use App\Http\Controllers\ConversationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$errors = 0;

$user = App\Models\User::query()->first();
if (!$user) {
    echo "SKIP: sem usuários\n";
    exit(0);
}

Auth::login($user);

$contactWithMany = Conversation::query()
    ->select('contact_id')
    ->groupBy('contact_id')
    ->havingRaw('COUNT(*) > 1')
    ->value('contact_id');

if (!$contactWithMany) {
    echo "SKIP: nenhum contato com múltiplas conversas\n";
    exit(0);
}

$conversations = Conversation::with(['activeClaim.user', 'contact', 'lastMessage'])
    ->where('contact_id', $contactWithMany)
    ->orderByDesc('last_message_at')
    ->get();

$controller = new ConversationController();
$method = new ReflectionMethod($controller, 'dedupeConversationsByContact');
$method->setAccessible(true);

$deduped = $method->invoke($controller, $conversations, (int) $user->id);

if ($deduped->count() !== 1) {
    echo "FAIL: esperava 1 conversa após dedupe, veio {$deduped->count()}\n";
    $errors++;
} else {
    $picked = $deduped->first();
    $mine = $conversations->first(fn ($c) => $c->hasActiveClaim((int) $user->id));
    $claimed = $conversations->first(fn ($c) => $c->getActiveClaim());

    $expected = $mine ?? $claimed ?? $conversations->first();
    if ((int) $picked->id !== (int) $expected->id) {
        echo "FAIL: dedupe escolheu #{$picked->id}, esperado #{$expected->id}\n";
        $errors++;
    } else {
        echo "OK: dedupe escolheu conversa #{$picked->id} para contato #{$contactWithMany}\n";
    }
}

$resolveMethod = new ReflectionMethod($controller, 'resolveConversationForContact');
$resolveMethod->setAccessible(true);

$wrong = $conversations->first(fn ($c) => ! $c->hasActiveClaim((int) $user->id)) ?? $conversations->first();
$resolved = $resolveMethod->invoke($controller, $wrong, (int) $user->id);

if ($mine && (int) $resolved->id !== (int) $mine->id) {
    echo "FAIL: resolve não apontou para conversa com claim do usuário\n";
    $errors++;
} elseif ($mine) {
    echo "OK: resolve redireciona para conversa #{$resolved->id}\n";
} else {
    echo "OK: resolve manteve conversa #{$resolved->id}\n";
}

exit($errors > 0 ? 1 : 0);
