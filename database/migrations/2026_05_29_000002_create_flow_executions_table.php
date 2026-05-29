<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flow_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations');
            $table->foreignId('flow_id')->constrained('conversation_flows');
            $table->foreignId('node_id')->nullable()->constrained('flow_nodes')->nullOnDelete();
            $table->integer('client_choice')->nullable();
            $table->enum('status', ['started', 'in_progress', 'completed', 'failed'])->default('in_progress');
            $table->foreignId('result_sector_id')->nullable()->constrained('sectors')->nullOnDelete();
            $table->foreignId('result_subflow_id')->nullable()->constrained('conversation_flows')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_executions');
    }
};
