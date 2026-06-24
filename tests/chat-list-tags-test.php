<?php

/**
 * Teste: lista lateral exibe etiquetas, não setor.
 * Uso: php tests/chat-list-tags-test.php
 */

$errors = 0;

function ok(bool $cond, string $msg): void
{
    global $errors;
    echo ($cond ? 'OK' : 'FAIL') . ": {$msg}\n";
    if (!$cond) {
        $errors++;
    }
}

$blade = file_get_contents(__DIR__ . '/../resources/views/components/chat/list-item.blade.php');
$js = file_get_contents(__DIR__ . '/../public/js/helpers/chat-list-poller.js');
$controller = file_get_contents(__DIR__ . '/../app/Http/Controllers/ConversationController.php');

ok(str_contains($blade, 'chat-list-chip--tag'), 'blade usa chip de etiqueta');
ok(!str_contains($blade, 'chat-list-chip--sector'), 'blade não usa chip de setor');
ok(str_contains($blade, '$tags as $tag'), 'blade itera etiquetas');
ok(str_contains($js, 'buildTagChipsHtml'), 'poller monta chips de etiqueta');
ok(str_contains($controller, "'tags' => \$conv->tags"), 'API retorna etiquetas');

exit($errors > 0 ? 1 : 0);
