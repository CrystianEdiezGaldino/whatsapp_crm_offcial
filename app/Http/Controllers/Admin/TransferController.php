<?php

namespace App\Http\Controllers\Admin;

use App\Models\ConversationTransfer;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class TransferController
{
    public function index()
    {
        $transfers = ConversationTransfer::query()
            ->with('conversation', 'fromUser', 'toUser', 'fromSector', 'toSector')
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'pending' => ConversationTransfer::where('status', 'pending')->count(),
            'completed' => ConversationTransfer::where('status', 'completed')->count(),
            'rejected' => ConversationTransfer::where('status', 'rejected')->count(),
        ];

        return view('admin.transfers.index', compact('transfers', 'stats'));
    }

    public function show(ConversationTransfer $transfer)
    {
        $transfer->load('conversation', 'fromUser', 'toUser', 'fromSector', 'toSector', 'requestedBy', 'approvedBy');

        return view('admin.transfers.show', compact('transfer'));
    }

    public function approve(ConversationTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Apenas transferências pendentes podem ser aprovadas');
        }

        $transfer->approve(auth()->user());
        $transfer->conversation->update(['status' => 'in_progress', 'assigned_to' => $transfer->to_user_id]);

        return redirect()->route('admin.transfers.index')->with('success', 'Transferência aprovada');
    }

    public function reject(Request $request, ConversationTransfer $transfer)
    {
        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Apenas transferências pendentes podem ser rejeitadas');
        }

        $reason = $request->input('reason', 'Sem motivo especificado');
        $transfer->reject($reason);

        return redirect()->route('admin.transfers.index')->with('success', 'Transferência rejeitada');
    }

    public function complete(ConversationTransfer $transfer)
    {
        if ($transfer->status !== 'approved') {
            return redirect()->back()->with('error', 'Apenas transferências aprovadas podem ser completadas');
        }

        $transfer->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.transfers.index')->with('success', 'Transferência concluída');
    }

    public function pending()
    {
        $transfers = ConversationTransfer::pending()
            ->with('conversation', 'fromUser', 'toUser')
            ->orderByDesc('requested_at')
            ->get();

        return view('admin.transfers.pending', compact('transfers'));
    }

    public function analytics()
    {
        $byReason = ConversationTransfer::query()
            ->selectRaw('reason, COUNT(*) as count')
            ->whereNotNull('reason')
            ->groupBy('reason')
            ->get();

        $byAgent = ConversationTransfer::query()
            ->selectRaw('to_user_id, COUNT(*) as count')
            ->groupBy('to_user_id')
            ->with('toUser')
            ->get();

        $averageResolutionTime = ConversationTransfer::completed()
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, requested_at, completed_at)) as avg_time')
            ->value('avg_time');

        return view('admin.transfers.analytics', compact(
            'byReason',
            'byAgent',
            'averageResolutionTime'
        ));
    }
}
