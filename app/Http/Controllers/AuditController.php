<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function timeline(Conversation $conversation)
    {
        if (!Auth::user()->isAdmin() && $conversation->assigned_to !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Permissão negada'], 403);
        }

        $logs = AuditLog::where('auditable_id', $conversation->id)
            ->where('auditable_type', 'Conversation')
            ->orWhere(function ($query) use ($conversation) {
                $query->where('auditable_type', 'Message')
                      ->whereIn('auditable_id', $conversation->messages()->pluck('id'));
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user_name' => $log->user?->name ?? 'Sistema',
                'created_at' => $log->created_at,
                'created_at_formatted' => $log->created_at->format('d/m/Y H:i:s'),
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
            ]);

        return response()->json([
            'success' => true,
            'timeline' => $logs,
        ]);
    }

    public function conversation(Request $request)
    {
        $query = AuditLog::query()
            ->where('auditable_type', 'Conversation');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id') && Auth::user()->isAdmin()) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    public function agentActivity(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Apenas admins'], 403);
        }

        $logs = AuditLog::query()
            ->whereIn('action', ['created', 'updated', 'claimed', 'released', 'assigned'])
            ->where('auditable_type', 'Conversation')
            ->where('user_id', '!=', null)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id')
            ->map(function ($userLogs) {
                $user = $userLogs->first()->user;
                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'total_actions' => $userLogs->count(),
                    'actions' => $userLogs->groupBy('action')->map(fn($g) => [
                        'action' => $g->first()->action,
                        'count' => $g->count(),
                    ]),
                ];
            });

        return response()->json([
            'success' => true,
            'activity' => $logs->values(),
        ]);
    }
}
