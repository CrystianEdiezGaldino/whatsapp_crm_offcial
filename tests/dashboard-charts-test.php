<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::first();
if (!$user) {
    fwrite(STDERR, "FALHA: nenhum usuário no banco\n");
    exit(1);
}
auth()->login($user);

$controller = app(App\Http\Controllers\ReportController::class);
$response = $controller->dashboardData(new Illuminate\Http\Request());
$data = json_decode($response->getContent(), true);

if (!isset($data['by_direction']) || !is_array($data['by_direction'])) {
    fwrite(STDERR, "FALHA: by_direction ausente\n");
    exit(1);
}

$blade = file_get_contents(dirname(__DIR__) . '/resources/views/dashboard.blade.php');
if (preg_match("/getElementById\('topContact'\)\.textContent/", $blade)) {
    fwrite(STDERR, "FALHA: topContact sem guard pode quebrar gráficos\n");
    exit(1);
}

echo "OK: dashboard-data retorna by_direction (" . count($data['by_direction']) . " itens)\n";
