<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('last_message_at');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->string('block_reason')->nullable()->after('blocked_at');

            $table->index('is_blocked');
            $table->index('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['is_blocked']);
            $table->dropIndex(['blocked_at']);
            $table->dropColumn(['is_blocked', 'blocked_at', 'block_reason']);
        });
    }
};
