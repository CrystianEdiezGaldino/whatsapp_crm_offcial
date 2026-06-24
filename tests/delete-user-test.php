<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$email = 'ti@santamonica.rec.br';
$user = User::where('email', $email)->first();

if (!$user) {
    echo "Usuario nao encontrado: {$email}\n";
    exit(1);
}

echo "Encontrado: #{$user->id} {$user->name} ({$user->email}) role={$user->role}\n";

try {
    DB::transaction(function () use ($user) {
        $fallback = User::where('id', '!=', $user->id)
            ->whereIn('role', ['admin', 'supervisor'])
            ->orderBy('id')
            ->first();

        $schema = DB::getSchemaBuilder();

        if ($schema->hasColumn('conversations', 'claimed_by')) {
            $n = DB::table('conversations')->where('claimed_by', $user->id)->update([
                'claimed_by' => null,
                'claimed_at' => null,
            ]);
            if ($n) {
                echo "Liberadas {$n} conversas (claimed_by)\n";
            }
        }

        if ($schema->hasColumn('conversations', 'assigned_to')) {
            DB::table('conversations')->where('assigned_to', $user->id)->update(['assigned_to' => null]);
        }

        if ($schema->hasColumn('conversations', 'owner_id')) {
            DB::table('conversations')->where('owner_id', $user->id)->update(['owner_id' => null]);
        }

        if ($schema->hasColumn('conversations', 'assigned_by')) {
            DB::table('conversations')->where('assigned_by', $user->id)->update(['assigned_by' => null]);
        }

        if ($schema->hasColumn('contacts', 'assigned_to')) {
            DB::table('contacts')->where('assigned_to', $user->id)->update(['assigned_to' => null]);
        }

        if ($schema->hasTable('conversation_flows')) {
            $count = DB::table('conversation_flows')->where('created_by', $user->id)->count();
            if ($count > 0 && $fallback) {
                DB::table('conversation_flows')->where('created_by', $user->id)->update(['created_by' => $fallback->id]);
                echo "Fluxos reatribuidos para user #{$fallback->id}\n";
            }
        }

        $user->delete();
    });

    echo "OK: usuario deletado.\n";
    exit(0);
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
