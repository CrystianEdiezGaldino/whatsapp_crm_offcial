<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'sector_id')) {
                $table->unsignedBigInteger('sector_id')->nullable()->after('role');
                $table->foreign('sector_id')->references('id')->on('sectors');
                $table->index('sector_id');
            }

            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sector_id');
                $table->index('is_active');
            }

            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'sector_id')) {
                $table->dropForeign(['sector_id']);
                $table->dropIndex(['sector_id']);
                $table->dropColumn('sector_id');
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropIndex(['is_active']);
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('users', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
