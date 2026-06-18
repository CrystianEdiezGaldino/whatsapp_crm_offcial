<?php
/**
 * Teste: verifica se contato/conversas foram removidos.
 * Uso: php8.3 tests/delete-contact-by-phone-test.php
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\DB;

$phone = '5511999234029';
$variants = PhoneNormalizer::variants($phone);
$contacts = DB::table('contacts')->whereIn('phone', $variants)->count();
$convs = DB::table('conversations')
    ->whereIn('contact_id', DB::table('contacts')->whereIn('phone', $variants)->pluck('id'))
    ->count();

$ok = $contacts === 0;
echo ($ok ? 'PASS' : 'FAIL') . ": contacts=$contacts conversations=$convs\n";
exit($ok ? 0 : 1);
