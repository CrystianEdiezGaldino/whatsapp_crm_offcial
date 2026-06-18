<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('complaints')) {
            return;
        }

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained();
            $table->foreignId('responsible_user_id')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->integer('rating')->between(1, 5);
            $table->text('customer_note')->nullable();
            $table->enum('severity', ['low', 'medium', 'high']);
            $table->enum('status', ['open', 'reviewing', 'resolved', 'dismissed'])->default('open');
            $table->text('review_notes')->nullable();
            $table->enum('action_taken', ['coaching', 'retraining', 'suspension', 'none'])->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['severity', 'status']);
            $table->index('responsible_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
