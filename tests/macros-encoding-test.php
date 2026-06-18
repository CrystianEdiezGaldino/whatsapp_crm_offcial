<?php
/** Teste: macros JSON e encoding Você no blade. */
$blade = file_get_contents(__DIR__ . '/../resources/views/conversations/index.blade.php');
$ok = str_contains($blade, '(Você)')
    && str_contains($blade, 'initMacrosMenu')
    && file_exists(__DIR__ . '/../public/js/helpers/macros-menu.js');
echo ($ok ? 'PASS' : 'FAIL') . ": macros+encoding\n";
exit($ok ? 0 : 1);
