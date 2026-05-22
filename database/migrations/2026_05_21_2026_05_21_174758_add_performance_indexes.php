<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index para queries de webhook
        Schema::table('messages', function (Blueprint $table) {
            $table->index('wa_message_id');
            $table->index('conversation_id');
            $table->index('created_at');
            $table->index(['created_at', 'direction']);
        });

        // Index para conversas abertas
        Schema::table('conversations', function (Blueprint $table) {
            $table->index('status');
            $table->index(['assigned_to', 'status']);
            $table->index('contact_id');
            $table->index('last_message_at');
        });

        // Index para contatos
        Schema::table('contacts', function (Blueprint $table) {
            $table->index('phone');
            $table->index('created_at');
        });

        // Index para usuários
        Schema::table('users', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['wa_message_id']);
            $table->dropIndex(['conversation_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['created_at', 'direction']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['contact_id']);
            $table->dropIndex(['last_message_at']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
