<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = Illuminate\Http\Request::create('/admin/complaints/dashboard', 'GET');
try {
    $route = Illuminate\Support\Facades\Route::getRoutes()->match($request);
    echo 'Matched: ' . $route->getName() . ' -> ' . $route->getActionName() . PHP_EOL;
} catch (Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
    fwrite(STDERR, "FALHA: rota não encontrada\n");
    exit(1);
}

$user = App\Models\User::where('role', 'admin')->first();
auth()->login($user);

$controller = app(App\Http\Controllers\Admin\ComplaintController::class);
$response = $controller->dashboard();
$html = $response->render();
if (!str_contains($html, 'Dashboard de Reclamações')) {
    fwrite(STDERR, "FALHA: view sem título esperado\n");
    exit(1);
}
echo "OK: dashboard renderiza\n";
