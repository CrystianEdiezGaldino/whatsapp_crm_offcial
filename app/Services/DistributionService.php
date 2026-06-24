<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\DistributionSetting;
use App\Models\AgentCapacity;
use App\Models\AuditLog;
use App\Events\ConversationStatusChanged;
use Illuminate\Support\Facades\Log;

class DistributionService
{
    public static function assign(Conversation $conversation): void
    {
        $settings = DistributionSetting::current();

        if ($settings->isManual()) {
            Log::info('[Distribution] Manual mode - lead will be claimed by agents', [
                'conversation_id' => $conversation->id,
            ]);
            return;
        }

        self::autoAssign($conversation);
    }

    public static function autoAssign(Conversation $conversation): void
    {
        $settings = DistributionSetting::current();

        Log::info('[Distribution] Auto mode - finding agent for lead', [
            'conversation_id' => $conversation->id,
            'overflow_action' => $settings->overflow_action,
        ]);

        // Get active AND online agents with capacity available
        $availableAgents = AgentCapacity::where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('status', 'online'))
            ->with('user')
            ->get()
            ->filter(fn($capacity) => $capacity->hasCapacity())
            ->sortBy(fn($capacity) => $capacity->round_robin_position);

        // If we have available agents, assign to the next in round-robin
        if ($availableAgents->count() > 0) {
            $selectedCapacity = $availableAgents->first();
            self::assignToAgent($conversation, $selectedCapacity);
            return;
        }

        // All agents are at capacity
        if ($settings->isNextAgentOverflow()) {
            // Find online agent with least load
            $agentCapacities = AgentCapacity::where('is_active', true)
                ->whereHas('user', fn($q) => $q->where('status', 'online'))
                ->with('user')
                ->get()
                ->sortBy(fn($capacity) => $capacity->activeConversationsCount());

            if ($agentCapacities->count() > 0) {
                $selectedCapacity = $agentCapacities->first();
                Log::info('[Distribution] All agents full - assigning to least loaded', [
                    'conversation_id' => $conversation->id,
                    'agent_id' => $selectedCapacity->user_id,
                    'load' => $selectedCapacity->activeConversationsCount() . '/' . $selectedCapacity->max_conversations,
                ]);
                self::assignToAgent($conversation, $selectedCapacity);
                return;
            }
        }

        // Queue mode or no online agents available - leave in queue
        Log::info('[Distribution] No online agents available or all full - keeping in queue', [
            'conversation_id' => $conversation->id,
        ]);
    }

    private static function assignToAgent(Conversation $conversation, AgentCapacity $capacity): void
    {
        $oldStatus = $conversation->status;
        $agentId = $capacity->user_id;

        // Claim the conversation for the agent
        $conversation->claim($agentId, 'Auto-distribuído pelo sistema');

        // Update conversation status to in_attendance
        $conversation->update([
            'status' => 'in_attendance',
            'claimed_by' => $agentId,
            'claimed_at' => now(),
        ]);

        // Update round-robin position for fair distribution
        $maxPosition = AgentCapacity::max('round_robin_position');
        $capacity->increment('round_robin_position', 1);

        // Reset if it gets too large (prevent integer overflow)
        if ($capacity->round_robin_position > 10000) {
            AgentCapacity::query()->update(['round_robin_position' => 0]);
        }

        // Log in audit
        AuditLog::create([
            'auditable_type' => 'Conversation',
            'auditable_id' => $conversation->id,
            'action' => 'auto_assigned',
            'description' => "Lead automatically assigned to {$capacity->user->name}",
            'user_id' => null, // System assignment
            'new_values' => [
                'status' => 'in_attendance',
                'claimed_by' => $agentId,
                'claimed_at' => $conversation->claimed_at,
            ],
            'ip_address' => null,
            'user_agent' => null,
        ]);

        // Dispatch event for real-time update
        event(new ConversationStatusChanged($conversation, $oldStatus));

        Log::info('[Distribution] Lead assigned to agent', [
            'conversation_id' => $conversation->id,
            'agent_id' => $agentId,
            'agent_name' => $capacity->user->name,
            'current_load' => $capacity->activeConversationsCount() . '/' . $capacity->max_conversations,
        ]);
    }

    public static function getQueuedConversations()
    {
        return Conversation::where('status', 'new')
            ->with(['contact', 'lastMessage'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($conv) => [
                'id' => $conv->id,
                'contact_name' => $conv->contact?->name ?? 'Sem contato',
                'last_message' => $conv->lastMessage?->content ?? 'Sem mensagens',
                'created_at' => $conv->created_at,
                'wait_time' => now()->diffInMinutes($conv->created_at),
            ]);
    }

    public static function getAgentMetrics()
    {
        return AgentCapacity::with('user')
            ->where('is_active', true)
            ->get()
            ->map(function ($capacity) {
                $activeCount = $capacity->activeConversationsCount();
                $isOnline = $capacity->user->isOnline();
                return [
                    'user_id' => $capacity->user_id,
                    'name' => $capacity->user->name,
                    'active_conversations' => $activeCount,
                    'max_conversations' => $capacity->max_conversations,
                    'available_slots' => $capacity->getAvailableSlots(),
                    'load_percent' => (int) (($activeCount / $capacity->max_conversations) * 100),
                    'is_full' => $capacity->isFull(),
                    'is_online' => $isOnline,
                    'can_receive' => $isOnline && !$capacity->isFull(),
                ];
            })
            ->sortBy('load_percent');
    }
}
