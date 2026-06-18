<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = \App\Models\User::where('email', 'admin@erp.com')->first();
echo $u ? "USER_OK id={$u->id}\n" : "USER_MISSING\n";
try {
    \Illuminate\Support\Facades\DB::select('SELECT 1');
    echo "DB_OK\n";
} catch (Throwable $e) {
    echo "DB_FAIL: " . $e->getMessage() . "\n";
}
