<?php
/** Teste: toggle Habilitar em fluxos usa botão clicável (não label+div). */
$partial = file_get_contents(__DIR__ . '/../resources/views/admin/flows/partials/final-message-toggle.blade.php');
$create = file_get_contents(__DIR__ . '/../resources/views/admin/flows/create.blade.php');
$index = file_get_contents(__DIR__ . '/../resources/views/admin/flows/index.blade.php');

$ok = str_contains($partial, 'id="finalMsgToggleBtn"')
    && str_contains($partial, 'type="button"')
    && str_contains($partial, 'onclick="toggleFinalMessage()"')
    && str_contains($partial, 'pointer-events-none')
    && str_contains($create, "admin.flows.partials.final-message-toggle")
    && str_contains($index, 'route(\'admin.flows.toggle\'')
    && str_contains($index, 'class="toggle-switch');

echo ($ok ? 'PASS' : 'FAIL') . ": flows habilitar toggle\n";
exit($ok ? 0 : 1);
