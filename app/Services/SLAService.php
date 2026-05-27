<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Sector;
use Carbon\Carbon;

class SLAService
{
    public function calculateSLA(Conversation $conversation): void
    {
        if (!$conversation->sector_id) {
            return;
        }

        $sector = $conversation->sector;

        // SLA de primeira resposta
        $firstResponseMinutes = $sector->sla_first_response_minutes ?? 15;
        $conversation->sla_first_response_expires_at = now()->addMinutes($firstResponseMinutes);

        // SLA de resolução
        $resolutionHours = $sector->sla_resolution_hours ?? 24;
        $conversation->sla_resolution_expires_at = now()->addHours($resolutionHours);

        $conversation->save();
    }

    public function checkBreaches(): void
    {
        // Breaches de primeira resposta
        Conversation::query()
            ->where('status', 'queued')
            ->whereNotNull('sla_first_response_expires_at')
            ->where('sla_first_response_expires_at', '<', now())
            ->where('sla_first_response_breached', false)
            ->each(function (Conversation $conversation) {
                $conversation->update([
                    'sla_first_response_breached' => true,
                    'priority_level' => 'urgent',
                ]);
            });

        // Breaches de resolução
        Conversation::query()
            ->whereIn('status', ['queued', 'in_progress', 'waiting_customer'])
            ->whereNotNull('sla_resolution_expires_at')
            ->where('sla_resolution_expires_at', '<', now())
            ->where('sla_resolution_breached', false)
            ->each(function (Conversation $conversation) {
                $conversation->update([
                    'sla_resolution_breached' => true,
                    'priority_level' => 'urgent',
                ]);
            });
    }

    public function getConversationsSLAStatus($sectorId = null)
    {
        $query = Conversation::query()
            ->whereIn('status', ['queued', 'in_progress', 'waiting_customer'])
            ->with('sector', 'owner');

        if ($sectorId) {
            $query->where('sector_id', $sectorId);
        }

        return $query->get()->map(function (Conversation $conversation) {
            return [
                'id' => $conversation->id,
                'contact' => $conversation->contact->name,
                'sector' => $conversation->sector->name,
                'status' => $conversation->status,
                'priority' => $conversation->priority_level,
                'first_response' => [
                    'expires_at' => $conversation->sla_first_response_expires_at,
                    'breached' => $conversation->sla_first_response_breached,
                    'time_remaining' => $this->getTimeRemaining($conversation->sla_first_response_expires_at),
                ],
                'resolution' => [
                    'expires_at' => $conversation->sla_resolution_expires_at,
                    'breached' => $conversation->sla_resolution_breached,
                    'time_remaining' => $this->getTimeRemaining($conversation->sla_resolution_expires_at),
                ],
            ];
        });
    }

    private function getTimeRemaining(?Carbon $expiresAt): ?string
    {
        if (!$expiresAt) {
            return null;
        }

        if ($expiresAt->isPast()) {
            return 'Expirado há ' . $expiresAt->diffForHumans();
        }

        return 'Expira em ' . $expiresAt->diffForHumans();
    }
}
