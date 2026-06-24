<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\AudioMediaPreparer;

echo 'ffmpeg_path=' . AudioMediaPreparer::ffmpegBinary() . PHP_EOL;
echo 'ffmpeg_available=' . (AudioMediaPreparer::ffmpegAvailable() ? 'yes' : 'no') . PHP_EOL;
echo 'temp=' . sys_get_temp_dir() . PHP_EOL;
echo 'os=' . PHP_OS_FAMILY . PHP_EOL;

$tmpWebm = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'diag_test.webm';
$tmpMp3 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'diag_test_out.mp3';
$bin = AudioMediaPreparer::ffmpegBinary();

// gera webm sintético
$gen = sprintf('"%s" -y -f lavfi -i sine=frequency=440:duration=1 -c:a libopus %s 2>&1', $bin, escapeshellarg($tmpWebm));
exec($gen, $genOut, $genCode);
echo 'gen_webm_code=' . $genCode . PHP_EOL;

if ($genCode === 0 && is_file($tmpWebm)) {
    $cmd = sprintf('"%s" -y -i %s -c:a libmp3lame -b:a 128k %s 2>&1', $bin, escapeshellarg($tmpWebm), escapeshellarg($tmpMp3));
    exec($cmd, $out, $code);
    echo 'convert_code=' . $code . PHP_EOL;
    echo 'mp3_exists=' . (is_file($tmpMp3) ? 'yes' : 'no') . PHP_EOL;
    if ($code !== 0) {
        echo implode("\n", array_slice($out, 0, 8)) . PHP_EOL;
    }
    @unlink($tmpWebm);
    @unlink($tmpMp3);
} else {
    echo 'gen_failed=' . implode(' | ', array_slice($genOut, 0, 5)) . PHP_EOL;
}
