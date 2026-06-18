<?php
/**
 * Remove contato(s) e conversas de um telefone.
 * Uso: php delete-contact-by-phone.php 5511999234029
 */
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Contact;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\DB;

$phone = $argv[1] ?? '';
if ($phone === '') {
    fwrite(STDERR, "Uso: php delete-contact-by-phone.php <telefone>\n");
    exit(1);
}

$variants = PhoneNormalizer::variants($phone);
echo 'Variantes: ' . implode(', ', $variants) . "\n";

$contacts = Contact::whereIn('phone', $variants)->get();
if ($contacts->isEmpty()) {
    echo "Nenhum contato encontrado.\n";
    exit(0);
}

foreach ($contacts as $contact) {
    echo "Contato #{$contact->id} {$contact->name} ({$contact->phone})\n";
    $convIds = DB::table('conversations')->where('contact_id', $contact->id)->pluck('id');
    echo "  Conversas: " . $convIds->count() . "\n";

    DB::transaction(function () use ($contact, $convIds) {
        foreach ($convIds as $cid) {
            DB::table('flow_executions')->where('conversation_id', $cid)->delete();
            DB::table('complaints')->where('conversation_id', $cid)->delete();
            DB::table('conversation_transfers')->where('conversation_id', $cid)->delete();
            DB::table('conversation_reopen_requests')->where('conversation_id', $cid)->delete();
            DB::table('conversation_resolutions')->where('conversation_id', $cid)->delete();
            DB::table('conversation_claims')->where('conversation_id', $cid)->delete();
            DB::table('conversation_tags')->where('conversation_id', $cid)->delete();
            DB::table('messages')->where('conversation_id', $cid)->delete();
            DB::table('conversations')->where('id', $cid)->delete();
        }
        $contact->delete();
    });

    echo "  Removido.\n";
}

echo "OK\n";
