<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('distribution_settings')) {
            Schema::create('distribution_settings', function (Blueprint $table) {
                $table->id();
                $table->enum('mode', ['manual', 'automatic'])->default('manual');
                $table->enum('overflow_action', ['next_agent', 'queue'])->default('next_agent');
                $table->timestamps();
            });
        }

        if (DB::table('distribution_settings')->count() === 0) {
            if (DB::getDriverName() === 'sqlsrv') {
                DB::statement(
                    "INSERT INTO distribution_settings (mode, overflow_action, created_at, updated_at) VALUES (?, ?, GETDATE(), GETDATE())",
                    ['manual', 'next_agent']
                );
            } else {
                DB::table('distribution_settings')->insert([
                    'mode' => 'manual',
                    'overflow_action' => 'next_agent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_settings');
    }
};
