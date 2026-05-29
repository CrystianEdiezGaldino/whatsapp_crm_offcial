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
            $table->foreignId('target_sector_id')->nullable()->constrained('sectors')->nullOnDelete();
            $table->foreignId('target_flow_id')->nullable()->constrained('conversation_flows')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_nodes');
    }
};
