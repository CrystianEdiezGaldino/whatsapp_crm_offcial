<?php

$controller = file_get_contents(__DIR__ . '/../app/Http/Controllers/ConversationController.php');
$preparer = file_get_contents(__DIR__ . '/../app/Support/AudioMediaPreparer.php');

if (!str_contains($preparer, 'isAudioFile')) {
    fwrite(STDERR, "FALHA: AudioMediaPreparer sem isAudioFile\n");
    exit(1);
}

if (!str_contains($controller, 'AudioMediaPreparer::isAudioFile')) {
    fwrite(STDERR, "FALHA: controller não usa isAudioFile\n");
    exit(1);
}

if (!str_contains($controller, "'media_filename', 'mime_type'")) {
    fwrite(STDERR, "FALHA: poll sem mime_type/media_filename\n");
    exit(1);
}

echo "OK: correção de áudio (m4a/video/mp4) aplicada\n";
