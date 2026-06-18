<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('conversation_flows')) {
            Schema::create('conversation_flows', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['primary', 'secondary'])->default('primary');
                $table->enum('trigger_type', ['on_new_conversation', 'on_command', 'manual'])->default('on_new_conversation');
                $table->string('command_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('parent_flow_id')->nullable();
                $table->foreign('parent_flow_id')->references('id')->on('conversation_flows');
                $table->json('config')->nullable();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_flows');
    }
};
