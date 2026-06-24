<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_settings', function (Blueprint $table) {
            $table->string('bot_name', 100)->default('Assistente Virtual')->after('overflow_action');
        });
    }

    public function down(): void
    {
        Schema::table('distribution_settings', function (Blueprint $table) {
            $table->dropColumn('bot_name');
        });
    }
};
