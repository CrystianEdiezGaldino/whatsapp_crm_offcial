<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

view()->share('errors', session()->get('errors', new Illuminate\Support\ViewErrorBag()));

$user = App\Models\User::find(3);
if (!$user) {
    echo "FALHA: user id 3 nao existe\n";
    exit(1);
}

$html = view('admin.agents.edit', [
    'user' => $user,
    'sectors' => App\Models\Sector::active()->byOption()->get(),
    'roles' => ['agent' => 'Atendente', 'supervisor' => 'Supervisor', 'admin' => 'Administrador'],
])->render();

echo str_contains($html, $user->name) ? "OK: view edit renderiza user #{$user->id}\n" : "FALHA: nome nao encontrado\n";
