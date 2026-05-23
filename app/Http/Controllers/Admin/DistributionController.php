<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributionSetting;
use App\Models\AgentCapacity;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\DistributionService;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    public function index()
    {
        $settings = DistributionSetting::current();
        $agents = User::where('role', 'agent')
            ->with('agentCapacity')
            ->orderBy('name')
            ->get();

        $metrics = DistributionService::getAgentMetrics();
        $queuedLeads = DistributionService::getQueuedConversations();

        $recentAssignments = AuditLog::where('action', 'auto_assigned')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'conversation_id' => $log->auditable_id,
                'agent_name' => $log->new_values['claimed_by']
                    ? User::find($log->new_values['claimed_by'])?->name
                    : 'Desconhecido',
                'created_at' => $log->created_at,
                'time_ago' => $log->created_at->diffForHumans(),
            ]);

        return view('admin.distribution.index', compact(
            'settings',
            'agents',
            'metrics',
            'queuedLeads',
            'recentAssignments'
        ));
    }

    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'mode' => 'required|in:manual,automatic',
                'overflow_action' => 'required_if:mode,automatic|in:next_agent,queue',
            ]);

            // If manual mode, set a default overflow_action
            if ($validated['mode'] === 'manual') {
                $validated['overflow_action'] = $validated['overflow_action'] ?? 'next_agent';
            }

            $settings = DistributionSetting::current();
            $settings->update($validated);

            \Log::info('[Distribution] Settings updated', $validated);

            return redirect()->route('admin.distribution.index')
                ->with('success', 'Configurações de distribuição atualizadas com sucesso!');
        } catch (\Exception $e) {
            \Log::error('[Distribution] Error updating settings', ['error' => $e->getMessage()]);
            return redirect()->route('admin.distribution.index')
                ->with('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
        }
    }

    public function updateAgentCapacity(Request $request, User $user)
    {
        if (!$user->isAgent()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não é um agente',
            ], 422);
        }

        $validated = $request->validate([
            'max_conversations' => 'required|integer|min:1|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $capacity = AgentCapacity::where('user_id', $user->id)->first();
        if (!$capacity) {
            $capacity = AgentCapacity::create([
                'user_id' => $user->id,
                'max_conversations' => $validated['max_conversations'],
                'is_active' => $validated['is_active'] ?? true,
            ]);
        } else {
            $capacity->update($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Capacidade atualizada com sucesso!',
            'capacity' => [
                'user_id' => $capacity->user_id,
                'max_conversations' => $capacity->max_conversations,
                'is_active' => $capacity->is_active,
            ],
        ]);
    }

    public function metrics()
    {
        $settings = DistributionSetting::current();
        $metrics = DistributionService::getAgentMetrics();
        $queuedCount = DistributionService::getQueuedConversations()->count();

        return response()->json([
            'mode' => $settings->mode,
            'overflow_action' => $settings->overflow_action,
            'agents' => $metrics,
            'queued_count' => $queuedCount,
            'total_agents' => $metrics->count(),
            'agents_full' => $metrics->where('is_full', true)->count(),
        ]);
    }

    public function isAgent()
    {
        return $this->role === 'agent';
    }
}
