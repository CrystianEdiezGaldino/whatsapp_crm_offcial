<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('claimed_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index('conversation_id');
            $table->index('user_id');
            $table->index('claimed_at');
            $table->unique(['conversation_id', 'user_id', 'released_at'], 'unique_active_claim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_claims');
    }
};
