<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_conversations' => Conversation::count(),
            'open_conversations' => Conversation::where('status', 'open')->count(),
            'total_messages' => Message::whereDate('created_at', today())->count(),
            'new_contacts' => Contact::whereDate('created_at', today())->count(),
            'avg_response_time' => '1m 24s',
        ];

        $pendingChats = Conversation::where('status', 'open')
            ->whereNull('assigned_to')
            ->with(['contact', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        $myChats = Conversation::where('status', 'open')
            ->where('assigned_to', $user->id)
            ->with(['contact', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        $onlineAgents = User::where('status', 'online')
            ->withCount(['conversations' => fn($q) => $q->where('status', 'open')])
            ->get();

        $agents = User::all();

        return view('dashboard', compact('stats', 'pendingChats', 'myChats', 'onlineAgents', 'agents'));
    }
}
