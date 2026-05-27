<?php

namespace App\Http\Controllers\Admin;

use App\Models\Conversation;
use App\Services\SLAService;
use Illuminate\Http\Request;

class SLAController
{
    public function dashboard()
    {
        return view('admin.sla.dashboard');
    }

    public function metrics(SLAService $slaService)
    {
        $conversations = Conversation::query()
            ->whereIn('status', ['queued', 'in_progress', 'waiting_customer'])
            ->with('contact', 'sector')
            ->get();

        $totalOpen = $conversations->count();
        $firstResponseAtRisk = $conversations->filter(fn($c) => $c->sla_first_response_expires_at && !$c->sla_first_response_breached && $c->sla_first_response_expires_at->diffInMinutes(now()) <= 5)->count();
        $resolutionAtRisk = $conversations->filter(fn($c) => $c->sla_resolution_expires_at && !$c->sla_resolution_breached && $c->sla_resolution_expires_at->diffInMinutes(now()) <= 30)->count();
        $breaches = $conversations->filter(fn($c) => $c->sla_first_response_breached || $c->sla_resolution_breached)->count();

        $slaStatus = $slaService->getConversationsSLAStatus();

        $bySector = $conversations->groupBy('sector.name')->map(function ($conversations, $sectorName) {
            $breached = $conversations->filter(fn($c) => $c->sla_first_response_breached || $c->sla_resolution_breached)->count();

            return [
                'name' => $sectorName,
                'conversations_count' => $conversations->count(),
                'breaches' => $breached,
            ];
        })->values();

        $conversationsList = $conversations->map(function ($conversation) {
            return [
                'id' => $conversation->id,
                'contact' => $conversation->contact->name,
                'sector' => $conversation->sector->name ?? 'N/A',
                'priority' => $conversation->priority_level,
                'first_response_breached' => $conversation->sla_first_response_breached,
                'first_response_remaining' => $conversation->sla_first_response_expires_at ? $conversation->sla_first_response_expires_at->diffForHumans() : null,
                'resolution_breached' => $conversation->sla_resolution_breached,
                'resolution_remaining' => $conversation->sla_resolution_expires_at ? $conversation->sla_resolution_expires_at->diffForHumans() : null,
                'wait_time' => $conversation->entered_queue_at ? $conversation->entered_queue_at->diffForHumans() : null,
            ];
        });

        return response()->json([
            'total_open' => $totalOpen,
            'first_response_at_risk' => $firstResponseAtRisk,
            'resolution_at_risk' => $resolutionAtRisk,
            'breaches' => $breaches,
            'by_sector' => $bySector->toArray(),
            'conversations' => $conversationsList->toArray(),
        ]);
    }

    public function checkBreaches(SLAService $slaService)
    {
        $slaService->checkBreaches();

        return response()->json(['success' => true, 'message' => 'SLAs verificados']);
    }
}
