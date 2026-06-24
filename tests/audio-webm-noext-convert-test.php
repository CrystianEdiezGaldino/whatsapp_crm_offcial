<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\AudioMediaPreparer;

$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
$noExtPath = $tmpDir . 'wa_noext_' . uniqid();
$webmPath = $tmpDir . 'wa_test_' . uniqid() . '.webm';
$bin = AudioMediaPreparer::ffmpegBinary();

exec(sprintf('"%s" -y -f lavfi -i sine=frequency=440:duration=1 -c:a libopus %s 2>&1', $bin, escapeshellarg($webmPath)), $o, $c);
if ($c !== 0 || !is_file($webmPath)) {
    fwrite(STDERR, "FALHA: não gerou webm de teste\n");
    exit(1);
}

copy($webmPath, $noExtPath);

try {
    $prepared = AudioMediaPreparer::prepare($noExtPath, 'audio/webm', 'audio_test.webm', true, true);
    if (!is_file($prepared['path']) || $prepared['mime'] !== 'audio/mpeg') {
        fwrite(STDERR, "FALHA: prepare sem extensão não gerou mp3\n");
        exit(1);
    }
    echo "OK: webm sem extensão no disco converte para mp3\n";
} catch (Throwable $e) {
    fwrite(STDERR, "FALHA: " . $e->getMessage() . "\n");
    exit(1);
} finally {
    @unlink($webmPath);
    @unlink($noExtPath);
    if (isset($prepared['cleanup'])) {
        AudioMediaPreparer::deleteCleanup($prepared['cleanup']);
    }
}
