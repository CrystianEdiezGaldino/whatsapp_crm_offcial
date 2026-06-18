<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Only add claimed_by if it doesn't exist
            if (!Schema::hasColumn('conversations', 'claimed_by')) {
                $table->unsignedBigInteger('claimed_by')->nullable()->after('assigned_to');
                $table->foreign('claimed_by')->references('id')->on('users');
            }

            // Only add claimed_at if it doesn't exist
            if (!Schema::hasColumn('conversations', 'claimed_at')) {
                $table->timestamp('claimed_at')->nullable()->after('claimed_by');
            }

            // Add index for claimed_by if it doesn't exist
            if (!$this->indexExists('conversations', 'conversations_claimed_by_index')) {
                $table->index('claimed_by');
            }
        });

        // Update existing 'open' status to 'new' to match new system
        DB::table('conversations')
            ->where('status', 'open')
            ->update(['status' => 'new']);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $result = DB::select(
                "SHOW INDEX FROM {$table} WHERE Key_name = ?",
                [$indexName]
            );
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'claimed_by')) {
                $table->dropForeignKey('conversations_claimed_by_foreign');
                $table->dropColumn('claimed_by');
            }

            if (Schema::hasColumn('conversations', 'claimed_at')) {
                $table->dropColumn('claimed_at');
            }

            if ($this->indexExists('conversations', 'conversations_claimed_by_index')) {
                $table->dropIndex('conversations_claimed_by_index');
            }
        });

        // Revert 'new' status back to 'open'
        DB::table('conversations')
            ->where('status', 'new')
            ->update(['status' => 'open']);
    }
};
