<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('image_credits')->default(100)->after('is_active');
        });

        Schema::create('image_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('quality', 20);
            $table->unsignedSmallInteger('credits_used');
            $table->decimal('approximate_cost_usd', 8, 4);
            $table->string('external_job_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_credit_transactions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('image_credits');
        });
    }
};
