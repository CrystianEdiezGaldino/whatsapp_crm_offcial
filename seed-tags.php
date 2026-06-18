<?php

/**
 * Garante etiquetas padrão no banco (idempotente).
 * Uso: php seed-tags.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

(new Database\Seeders\TagSeeder())->run();

$count = App\Models\Tag::where('is_active', true)->count();
echo "OK: {$count} etiquetas ativas no banco.\n";
