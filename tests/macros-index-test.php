<?php
/** Teste: view macros recebe stats. php tests/macros-index-test.php */
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ref = new ReflectionMethod(App\Http\Controllers\MacroController::class, 'index');
$src = file_get_contents($ref->getFileName());
if (!str_contains($src, "'stats'")) {
    fwrite(STDERR, "FALHA: index não passa stats\n");
    exit(1);
}

$html = file_get_contents(dirname(__DIR__) . '/resources/views/macros/index.blade.php');
foreach (['macro-grid', 'category-pill', 'grid-cols-2 lg:grid-cols-4'] as $needle) {
    if (!str_contains($html, $needle)) {
        fwrite(STDERR, "FALHA: falta {$needle} na view\n");
        exit(1);
    }
}

echo "OK: grid macros configurado\n";
