<?php

$c = file_get_contents(__DIR__ . '/../app/Http/Controllers/ConversationController.php');

if (!str_contains($c, "\$type = 'document'")) {
    fwrite(STDERR, "FALHA: áudio deve enviar como document\n");
    exit(1);
}

if (!str_contains($c, 'asAttachment: true')) {
    fwrite(STDERR, "FALHA: prepare deve usar asAttachment\n");
    exit(1);
}

if (str_contains($c, 'sendAudio($phone')) {
    fwrite(STDERR, "FALHA: não deve usar sendAudio no outbound\n");
    exit(1);
}

echo "OK: áudio outbound como documento MP3 (API-only)\n";
