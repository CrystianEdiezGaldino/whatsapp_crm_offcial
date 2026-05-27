<?php

namespace App\Services;

use App\Models\Conversation;
use Carbon\Carbon;

class PriorityService
{
    public function calculatePriority(Conversation $conversation): string
    {
        $priority = 'normal';

        if (!$conversation->sector) {
            return $priority;
        }

        $rules = $conversation->sector->priority_rules ?? [];

        if (empty($rules)) {
            return $priority;
        }

        foreach ($rules as $rule) {
            if ($this->matchesRule($conversation, $rule)) {
                return $rule['priority'] ?? 'normal';
            }
        }

        return $priority;
    }

    private function matchesRule(Conversation $conversation, array $rule): bool
    {
        if (!isset($rule['condition'], $rule['value'])) {
            return false;
        }

        return match ($rule['condition']) {
            'keyword' => $this->hasKeyword($conversation, $rule['value']),
            'tag' => $this->hasTag($conversation, $rule['value']),
            'wait_time_minutes' => $this->exceeds_wait_time($conversation, $rule['value']),
            'contact_vip' => $this->isVIPContact($conversation),
            default => false,
        };
    }

    private function hasKeyword(Conversation $conversation, string $keyword): bool
    {
        $lastMessage = $conversation->lastMessage();
        if (!$lastMessage) {
            return false;
        }

        return str_contains(strtolower($lastMessage->body ?? ''), strtolower($keyword));
    }

    private function hasTag(Conversation $conversation, string $tagName): bool
    {
        return $conversation->tags()->where('name', $tagName)->exists();
    }

    private function exceeds_wait_time(Conversation $conversation, int $minutes): bool
    {
        if (!$conversation->entered_queue_at) {
            return false;
        }

        return $conversation->entered_queue_at->addMinutes($minutes)->isPast();
    }

    private function isVIPContact(Conversation $conversation): bool
    {
        return $conversation->contact?->is_vip ?? false;
    }

    public function escalatePriority(Conversation $conversation): void
    {
        $currentLevel = match ($conversation->priority_level) {
            'low' => 'normal',
            'normal' => 'high',
            'high' => 'urgent',
            'urgent' => 'critical',
            default => 'normal',
        };

        $conversation->update(['priority_level' => $currentLevel]);
    }

    public function checkAndEscalatePriorities(): void
    {
        Conversation::query()
            ->whereIn('status', ['queued', 'in_progress', 'waiting_customer'])
            ->where('priority_level', '!=', 'critical')
            ->each(function (Conversation $conversation) {
                if ($conversation->sla_first_response_breached) {
                    $this->escalatePriority($conversation);
                }
            });
    }
}
