<?php

$preparer = file_get_contents(__DIR__ . '/../app/Support/AudioMediaPreparer.php');
$controller = file_get_contents(__DIR__ . '/../app/Http/Controllers/ConversationController.php');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');

if (!str_contains($preparer, 'persistToPublicDisk')) {
    fwrite(STDERR, "FALHA: persistToPublicDisk ausente\n");
    exit(1);
}

if (!str_contains($controller, 'persistToPublicDisk')) {
    fwrite(STDERR, "FALHA: controller não persiste áudio\n");
    exit(1);
}

if (!str_contains($routes, 'storage.fallback')) {
    fwrite(STDERR, "FALHA: rota storage.fallback ausente\n");
    exit(1);
}

echo "OK: persistência de áudio + fallback /storage corrigidos\n";
