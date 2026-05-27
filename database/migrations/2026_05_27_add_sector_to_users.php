<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sector_id')->nullable()->after('role')->constrained('sectors')->onDelete('set null');
            $table->boolean('is_active')->default(true)->after('sector_id');
            $table->text('notes')->nullable()->after('is_active');

            $table->index('sector_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex(['sector_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['sector_id', 'is_active', 'notes']);
        });
    }
};
