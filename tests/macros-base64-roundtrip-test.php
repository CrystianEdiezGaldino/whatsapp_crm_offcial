<?php
/** Teste round-trip: base64+json igual ao blade. */
$payload = [
    'id' => 1,
    'name' => 'Teste "aspas"',
    'content' => "Linha 1\nLinha 2 com 'apóstrofo' e {chaves}",
    'shortcut' => '/oi',
    'category' => 'saudacao',
];
$encoded = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
$decoded = json_decode(base64_decode($encoded), true);
$ok = $decoded === $payload;
echo ($ok ? 'PASS' : 'FAIL') . ": macros base64 roundtrip\n";
exit($ok ? 0 : 1);
