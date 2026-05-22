<?php
/**
 * Teste rápido: BOM removido + ReportController carrega.
 * Uso: php tests/api-json-fix-test.php
 */
$root = dirname(__DIR__);
$reportFile = $root . '/app/Http/Controllers/ReportController.php';

$bytes = file_get_contents($reportFile, false, null, 0, 3);
if ($bytes === "\xEF\xBB\xBF") {
    fwrite(STDERR, "FALHA: ReportController ainda tem BOM\n");
    exit(1);
}

require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (!class_exists(App\Http\Controllers\ReportController::class)) {
    fwrite(STDERR, "FALHA: ReportController não carrega\n");
    exit(1);
}

echo "OK: ReportController sem BOM e classe carregada\n";
exit(0);
