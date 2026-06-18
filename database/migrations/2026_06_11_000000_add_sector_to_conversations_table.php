<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'sector_id')) {
                $table->unsignedBigInteger('sector_id')->nullable()->after('contact_id');
                $table->foreign('sector_id')->references('id')->on('sectors');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'sector_id')) {
                try {
                    $table->dropForeign('conversations_sector_id_foreign');
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->dropColumn('sector_id');
            }
        });
    }
};
