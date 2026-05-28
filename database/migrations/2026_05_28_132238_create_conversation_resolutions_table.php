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
        Schema::create('conversation_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('resolved_by')->constrained('users')->onDelete('cascade');
            $table->enum('resolution_reason', [
                'problem_solved',      // Problema Resolvido
                'customer_satisfied',  // Cliente Satisfeito
                'follow_up_needed',    // Acompanhamento Necessário
                'transferred',         // Transferido
                'duplicate',           // Conversa Duplicada
                'spam',                // Spam/Abuso
                'no_response',         // Sem Resposta do Cliente
                'other',               // Outro
            ])->default('problem_solved');
            $table->text('resolution_notes')->nullable(); // O que foi feito
            $table->text('internal_comments')->nullable(); // Comentários internos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_resolutions');
    }
};
