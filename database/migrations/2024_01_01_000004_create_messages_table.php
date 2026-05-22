<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('wa_message_id')->nullable()->unique();
            $table->string('direction');
            $table->string('type')->default('text');
            $table->text('content')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_id')->nullable();
            $table->string('media_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('status')->default('sent');
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
