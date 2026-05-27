<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::query()
            ->with('conversation', 'responsible', 'reviewer')
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'open' => Complaint::whereIn('status', ['open', 'reviewing'])->count(),
            'high_severity' => Complaint::where('severity', 'high')->count(),
            'pending_review' => Complaint::where('status', 'open')->count(),
        ];

        return view('admin.complaints.index', compact('complaints', 'stats'));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load('conversation', 'responsible', 'reviewer');

        return view('admin.complaints.show', compact('complaint'));
    }

    public function review(Complaint $complaint)
    {
        if ($complaint->status !== 'open') {
            return redirect()->back()->with('error', 'Apenas reclamações abertas podem ser revisadas');
        }

        $complaint->update(['status' => 'reviewing', 'reviewed_by' => auth()->id()]);

        return view('admin.complaints.review', compact('complaint'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'responsible_user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|between:1,5',
            'customer_note' => 'nullable|string|max:1000',
            'severity' => 'required|in:low,medium,high',
        ]);

        Complaint::create($validated);

        return redirect()->route('admin.complaints.index')->with('success', 'Reclamação registrada');
    }

    public function resolve(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'review_notes' => 'required|string|max:1000',
            'action_taken' => 'required|in:coaching,retraining,suspension,none',
        ]);

        $complaint->update([
            'status' => 'resolved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
            'action_taken' => $validated['action_taken'],
        ]);

        return redirect()->route('admin.complaints.index')->with('success', 'Reclamação resolvida');
    }

    public function dismiss(Request $request, Complaint $complaint)
    {
        $complaint->update([
            'status' => 'dismissed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return redirect()->route('admin.complaints.index')->with('success', 'Reclamação descartada');
    }

    public function dashboard()
    {
        $openComplaints = Complaint::open()
            ->with(['conversation.contact', 'responsible'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $highSeverity = Complaint::where('severity', 'high')
            ->whereIn('status', ['open', 'reviewing'])
            ->count();

        $recentlyResolved = Complaint::where('status', 'resolved')
            ->with(['conversation.contact'])
            ->latest('reviewed_at')
            ->limit(5)
            ->get();

        $byResponsible = Complaint::query()
            ->selectRaw('responsible_user_id, COUNT(*) as count')
            ->whereIn('status', ['open', 'reviewing'])
            ->groupBy('responsible_user_id')
            ->with('responsible')
            ->get();

        return view('admin.complaints.dashboard', compact(
            'openComplaints',
            'highSeverity',
            'recentlyResolved',
            'byResponsible'
        ));
    }
}
