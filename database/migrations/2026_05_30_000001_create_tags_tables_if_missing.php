<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('color')->default('#666666');
                $table->enum('category', ['priority', 'status', 'outcome', 'custom'])->default('custom');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['category', 'is_active']);
            });
        }

        if (!Schema::hasTable('conversation_tags')) {
            Schema::create('conversation_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['conversation_id', 'tag_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_tags');
        Schema::dropIfExists('tags');
    }
};
