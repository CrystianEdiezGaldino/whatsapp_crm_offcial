<?php

/**
 * Rode NO SERVIDOR LINUX (como usuário do PHP/web):
 *   cd /caminho/do/projeto
 *   php tests/audio-converter-linux-test.php
 *
 * Ou como www-data:
 *   sudo -u www-data php tests/audio-converter-linux-test.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\AudioConverter;
use App\Support\AudioMediaPreparer;

echo 'php_user=' . (function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? '?') : get_current_user()) . PHP_EOL;
echo 'os=' . PHP_OS_FAMILY . PHP_EOL;
echo 'temp=' . sys_get_temp_dir() . PHP_EOL;
echo 'wa_ffmpeg_path=' . config('services.whatsapp.ffmpeg_path') . PHP_EOL;

$converter = app(AudioConverter::class);
echo 'binary=' . $converter->binary() . PHP_EOL;
echo 'binary_executable=' . (is_executable($converter->binary()) ? 'yes' : 'no') . PHP_EOL;
echo 'available=' . ($converter->isAvailable() ? 'yes' : 'no') . PHP_EOL;

$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
$webm = $tmpDir . 'wa_diag_' . uniqid() . '.webm';
$noExt = $tmpDir . 'wa_diag_' . uniqid();

$gen = Illuminate\Support\Facades\Process::timeout(30)->run([
    $converter->binary(), '-y', '-f', 'lavfi', '-i', 'sine=frequency=440:duration=1',
    '-c:a', 'libopus', $webm,
]);

if (!$gen->successful() || !is_file($webm)) {
    fwrite(STDERR, "FALHA: PHP não conseguiu executar ffmpeg\n");
    fwrite(STDERR, $gen->errorOutput() . $gen->output() . "\n");
    exit(1);
}

copy($webm, $noExt);

try {
    $prepared = AudioMediaPreparer::prepare($noExt, 'audio/webm', 'gravacao.webm', true, true);
    $size = is_file($prepared['path']) ? filesize($prepared['path']) : 0;
    if ($prepared['mime'] !== 'audio/mpeg' || $size < 100) {
        fwrite(STDERR, "FALHA: conversão não gerou MP3 válido (size={$size})\n");
        exit(1);
    }
    echo "convert_ok=mp3 {$size} bytes\n";
    echo "OK: servidor Linux pronto para enviar áudio\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'FALHA: ' . $e->getMessage() . PHP_EOL);
    exit(1);
} finally {
    @unlink($webm);
    @unlink($noExt);
    if (isset($prepared['cleanup'])) {
        AudioMediaPreparer::deleteCleanup($prepared['cleanup']);
    }
}
