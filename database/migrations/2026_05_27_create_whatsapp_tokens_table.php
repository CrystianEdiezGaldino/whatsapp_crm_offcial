<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_type')->default('access'); // access, refresh
            $table->longText('token_value')->encrypted();
            $table->unsignedBigInteger('expires_in')->nullable(); // segundos
            $table->timestamp('expires_at')->nullable();
            $table->string('scope')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_refreshed_at')->nullable();
            $table->integer('refresh_attempts')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('token_type');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_tokens');
    }
};
