<?php

$blade = file_get_contents(__DIR__ . '/../resources/views/layouts/app.blade.php');

if (!str_contains($blade, "auth()->user()->isAgent()) ? ['assigned' => 'mine']")) {
    fwrite(STDERR, "FALHA: sidebar não redireciona atendente para assigned=mine\n");
    exit(1);
}

if (!str_contains($blade, "route(\$item['route'], \$item['params'] ?? [])")) {
    fwrite(STDERR, "FALHA: nav não usa params no href\n");
    exit(1);
}

echo "OK: sidebar Atendimentos -> mine para agent no layout\n";
