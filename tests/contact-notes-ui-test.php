<?php

/**
 * Teste: UI de notas do painel do contato.
 * Uso: php tests/contact-notes-ui-test.php
 */

$errors = 0;

function assertContains(string $haystack, string $needle, string $msg): void
{
    global $errors;
    if (!str_contains($haystack, $needle)) {
        echo "FAIL: {$msg}\n";
        $errors++;
    } else {
        echo "OK: {$msg}\n";
    }
}

$blade = file_get_contents(__DIR__ . '/../resources/views/components/chat/contact-panel.blade.php');
$css = file_get_contents(__DIR__ . '/../resources/css/components.css');
$js = file_get_contents(__DIR__ . '/../public/js/helpers/contact-panel.js');

assertContains($blade, 'contact-panel__notes-box', 'blade tem container de notas');
assertContains($blade, 'contact-panel__notes-ai-btn', 'blade tem botão IA estilizado');
assertContains($blade, 'contact-panel__notes-counter', 'blade tem contador');
assertContains($css, 'contact-panel__notes-box--filled', 'css tem estado preenchido');
assertContains($js, 'syncNotesUi', 'js sincroniza estados da nota');

exit($errors > 0 ? 1 : 0);
