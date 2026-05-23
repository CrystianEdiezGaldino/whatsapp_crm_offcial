<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('max_conversations')->default(10);
            $table->boolean('is_active')->default(true);
            $table->integer('round_robin_position')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('round_robin_position');
        });

        // Create capacity record for each existing agent
        $agents = DB::table('users')->where('role', 'agent')->get();
        foreach ($agents as $agent) {
            DB::table('agent_capacities')->insert([
                'user_id' => $agent->id,
                'max_conversations' => 10,
                'is_active' => true,
                'round_robin_position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_capacities');
    }
};
