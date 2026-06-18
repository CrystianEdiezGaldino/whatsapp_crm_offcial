<?php
/** Teste: macros index usa base64 em data-macro (evita &quot; quebrando JSON.parse). */
$blade = file_get_contents(__DIR__ . '/../resources/views/macros/index.blade.php');

$ok = str_contains($blade, 'data-macro="{{ base64_encode(json_encode(')
    && str_contains($blade, 'parseMacroData(btn.dataset.macro)')
    && str_contains($blade, 'base64_encode(json_encode($macro->content')
    && str_contains($blade, 'JSON.parse(atob(this.dataset.content))')
    && !preg_match('/data-macro="\{\{ e\(json_encode/s', $blade);

echo ($ok ? 'PASS' : 'FAIL') . ": macros base64 data attrs\n";
exit($ok ? 0 : 1);
