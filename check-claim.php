<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = (int) ($argv[1] ?? 3);
$c = App\Models\Conversation::with(['activeClaim.user', 'claims'])->find($id);
if (!$c) {
    echo "conv not found\n";
    exit(1);
}
echo "status={$c->status}\n";
echo "claimed_by={$c->claimed_by}\n";
$ac = $c->getActiveClaim();
echo 'activeClaim=' . ($ac?->id ?? 'null') . "\n";
echo 'claim_user=' . ($ac?->user_id ?? 'null') . ' type=' . gettype($ac?->user_id) . "\n";
echo 'strict_eq_1=' . (($ac?->user_id === 1) ? 'true' : 'false') . "\n";
foreach ($c->claims as $cl) {
    echo "claim#{$cl->id} user={$cl->user_id} released=" . ($cl->released_at ?? 'null') . "\n";
}
