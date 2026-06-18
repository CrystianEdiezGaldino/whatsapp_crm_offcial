<?php
/** Teste: macros-menu.js sem erro de sintaxe. */
$js = file_get_contents(__DIR__ . '/../public/js/helpers/macros-menu.js');
$ok = str_contains($js, 'function normalizeShortcut')
    && str_contains($js, 'initialMacros')
    && !preg_match('/}\s+return \(s/', $js);
echo ($ok ? 'PASS' : 'FAIL') . ": macros-menu.js syntax\n";
exit($ok ? 0 : 1);
