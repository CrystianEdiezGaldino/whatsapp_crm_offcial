<?php
/**
 * Teste: user_id do SQL Server deve comparar com Auth id (int).
 * Uso: php tests/claim-user-id-cast-test.php
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ConversationClaim;

$claim = new ConversationClaim(['user_id' => '1']);
$casted = $claim->user_id;
$ok = is_int($casted) && $casted === 1;

echo ($ok ? 'PASS' : 'FAIL') . ': user_id cast=' . gettype($casted) . "\n";
exit($ok ? 0 : 1);
