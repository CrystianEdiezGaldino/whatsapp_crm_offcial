<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

class QueueService
{
    public function addToQueue(Conversation $conversation): void
    {
        $nextPosition = $this->getNextQueuePosition($conversation->sector_id);

        $conversation->update([
            'is_in_queue' => true,
            'entered_queue_at' => now(),
            'queue_position' => $nextPosition,
        ]);
    }

    public function removeFromQueue(Conversation $conversation): void
    {
        $conversation->update([
            'is_in_queue' => false,
            'queue_position' => null,
        ]);

        $this->reorderQueue($conversation->sector_id);
    }

    public function getNextQueuePosition(?int $sectorId): int
    {
        $maxPosition = Conversation::query()
            ->when($sectorId, fn ($q) => $q->where('sector_id', $sectorId))
            ->where('is_in_queue', true)
            ->max('queue_position') ?? 0;

        return $maxPosition + 1;
    }

    private function reorderQueue(?int $sectorId): void
    {
        $conversations = Conversation::query()
            ->when($sectorId, fn ($q) => $q->where('sector_id', $sectorId))
            ->where('is_in_queue', true)
            ->orderBy('queue_position')
            ->get();

        foreach ($conversations as $index => $conversation) {
            $conversation->update(['queue_position' => $index + 1]);
        }
    }

    public function getQueueStatus(?int $sectorId = null)
    {
        return Conversation::query()
            ->when($sectorId, fn ($q) => $q->where('sector_id', $sectorId))
            ->where('is_in_queue', true)
            ->orderBy('queue_position')
            ->with('contact', 'sector')
            ->get()
            ->map(function (Conversation $conversation) {
                $waitMinutes = $conversation->entered_queue_at?->diffInMinutes(now()) ?? 0;

                return [
                    'id' => $conversation->id,
                    'contact' => $conversation->contact->name,
                    'sector' => $conversation->sector->name,
                    'position' => $conversation->queue_position,
                    'entered_at' => $conversation->entered_queue_at,
                    'wait_time_minutes' => $waitMinutes,
                    'priority' => $conversation->priority_level,
                ];
            });
    }

    public function getAverageWaitTime(?int $sectorId = null): int
    {
        return (int) Conversation::query()
            ->when($sectorId, fn ($q) => $q->where('sector_id', $sectorId))
            ->whereNotNull('entered_queue_at')
            ->whereNotNull('last_interaction_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, entered_queue_at, last_interaction_at)) as avg_wait')
            ->value('avg_wait') ?? 0;
    }

    public function processQueueByCapacity(?int $sectorId = null): void
    {
        $queuedConversations = Conversation::query()
            ->when($sectorId, fn ($q) => $q->where('sector_id', $sectorId))
            ->where('is_in_queue', true)
            ->orderBy('priority_level', 'desc')
            ->orderBy('queue_position')
            ->get();

        foreach ($queuedConversations as $conversation) {
            $nextAgent = $this->findAvailableAgent($conversation->sector_id);

            if ($nextAgent) {
                $conversation->claim($nextAgent->id, 'Auto-atribuído da fila');
                $this->removeFromQueue($conversation);
            } else {
                break;
            }
        }
    }

    private function findAvailableAgent(?int $sectorId)
    {
        return DB::table('users')
            ->where('sector_id', $sectorId)
            ->where('is_active', true)
            ->whereIn('role', ['agent', 'supervisor'])
            ->selectRaw('users.*, COUNT(DISTINCT conversations.id) as active_conversations')
            ->leftJoin('conversations', function ($join) {
                $join->on('conversations.claimed_by', '=', 'users.id')
                    ->whereIn('conversations.status', ['in_progress', 'waiting_customer']);
            })
            ->groupBy('users.id')
            ->havingRaw('active_conversations < 10')
            ->orderBy('active_conversations')
            ->first();
    }
}
