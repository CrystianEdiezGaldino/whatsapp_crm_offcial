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
            $table->unsignedBigInteger('node_id')->nullable();
            $table->foreign('node_id')->references('id')->on('flow_nodes');
            $table->integer('client_choice')->nullable();
            $table->enum('status', ['started', 'in_progress', 'completed', 'failed'])->default('in_progress');
            $table->unsignedBigInteger('result_sector_id')->nullable();
            $table->unsignedBigInteger('result_subflow_id')->nullable();
            $table->foreign('result_sector_id')->references('id')->on('sectors');
            $table->foreign('result_subflow_id')->references('id')->on('conversation_flows');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_executions');
    }
};
