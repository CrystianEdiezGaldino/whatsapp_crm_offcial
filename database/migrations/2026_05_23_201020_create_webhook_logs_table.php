<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['message', 'status_update', 'unknown'])->default('unknown');
            $table->enum('status', ['received', 'processing', 'success', 'failed'])->default('received');
            $table->string('wa_message_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('payload');
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('processing_time_ms')->default(0);
            $table->timestamps();
            $table->index('status');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
