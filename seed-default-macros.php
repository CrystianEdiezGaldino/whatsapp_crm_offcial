<?php
/**
 * Insere macros padrao do admin se ainda nao existirem.
 * Uso: php8.3 seed-default-macros.php
 */
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Macro;
use App\Models\User;

$defaults = [
    ['name' => 'Saudação', 'content' => 'Olá! Como posso ajudar você hoje?', 'shortcut' => 'oi', 'category' => 'saudacao'],
    ['name' => 'Aguarde', 'content' => 'Por favor, aguarde um momento enquanto verifico.', 'shortcut' => 'aguarde', 'category' => 'util'],
    ['name' => 'Encerramento', 'content' => 'Obrigado pelo contato! Estamos à disposição.', 'shortcut' => 'tchau', 'category' => 'encerramento'],
    ['name' => 'Horário', 'content' => 'Nosso horário é de segunda a sexta, das 8h às 18h.', 'shortcut' => 'horario', 'category' => 'util'],
];

$admin = User::where('role', 'admin')->first();
$userId = $admin?->id ?? 1;

$created = 0;
foreach ($defaults as $row) {
    if (Macro::where('user_id', $userId)->where('shortcut', $row['shortcut'])->exists()) {
        continue;
    }
    Macro::create([
        'user_id' => $userId,
        'name' => $row['name'],
        'content' => $row['content'],
        'shortcut' => $row['shortcut'],
        'category' => $row['category'],
    ]);
    $created++;
}

echo "OK: criadas=$created total=" . Macro::count() . " admin_id=$userId\n";
