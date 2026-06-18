<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flow_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('conversation_flows')->cascadeOnDelete();
            $table->enum('node_type', ['message', 'menu', 'action'])->default('message');
            $table->integer('position');
            $table->json('config');
            $table->unsignedBigInteger('target_sector_id')->nullable();
            $table->unsignedBigInteger('target_flow_id')->nullable();
            $table->foreign('target_sector_id')->references('id')->on('sectors');
            $table->foreign('target_flow_id')->references('id')->on('conversation_flows');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_nodes');
    }
};
