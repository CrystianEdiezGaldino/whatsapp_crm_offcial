<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // GET /reports/dashboard-data
    public function dashboardData(Request $request)
    {
        $startDate = $request->query('start_date') ?: now()->subDays(30)->startOfDay();
        $endDate = $request->query('end_date') ?: now()->endOfDay();
        $agentId = $request->query('agent_id', null);

        // Ensure dates are Carbon instances
        if (is_string($startDate)) {
            $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
        }
        if (is_string($endDate)) {
            $endDate = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        // Cache key única por parâmetros
        $cacheKey = 'reports:dashboard:' . md5($startDate->toDateTimeString() . $endDate->toDateTimeString() . $agentId);

        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $agentId) {
            return $this->computeDashboardData($startDate, $endDate, $agentId);
        });

        return response()->json($data);
    }

    private function computeDashboardData($startDate, $endDate, $agentId)
    {

        $query = Message::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($agentId) {
            $query->whereHas('conversation', fn($q) => $q->where('assigned_to', $agentId));
        }

        // 1. Mensagens por hora
        $byHour = Message::selectRaw("FORMAT(created_at, 'yyyy-MM-dd HH:00') as hour, COUNT(*) as count")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->groupBy("FORMAT(created_at, 'yyyy-MM-dd HH:00')")
            ->orderBy("FORMAT(created_at, 'yyyy-MM-dd HH:00')")
            ->get();

        // 2. Mensagens por tipo
        $byType = Message::selectRaw('type, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->groupBy('type')
            ->get();

        // 3. Inbound vs outbound
        $byDirection = Message::selectRaw('direction, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->groupBy('direction')
            ->get();

        // 4. Status de delivery
        $byStatus = Message::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->groupBy('status')
            ->get();

        // 5. Conversas por agente
        $byAgent = Conversation::selectRaw('users.name, COUNT(DISTINCT conversations.id) as count')
            ->leftJoin('users', 'conversations.assigned_to', '=', 'users.id')
            ->whereHas('messages', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->groupBy('conversations.assigned_to', 'users.name')
            ->get();

        // 6. Top contatos
        $topContacts = Contact::selectRaw('contacts.name, contacts.phone, COUNT(DISTINCT conversations.id) as conversations, COUNT(messages.id) as messages')
            ->leftJoin('conversations', 'contacts.id', '=', 'conversations.contact_id')
            ->leftJoin('messages', 'conversations.id', '=', 'messages.conversation_id')
            ->whereBetween('messages.created_at', [$startDate, $endDate])
            ->groupBy('contacts.id', 'contacts.name', 'contacts.phone')
            ->orderByDesc('messages')
            ->limit(10)
            ->get();

        // 7. Tempo médio de resposta
        $avgResponseTime = Message::selectRaw('AVG(TIMESTAMPDIFF(SECOND, inbound.created_at, outbound.created_at)) as seconds')
            ->from('messages as outbound')
            ->join('messages as inbound', 'outbound.conversation_id', '=', 'inbound.conversation_id')
            ->where('inbound.direction', 'inbound')
            ->where('outbound.direction', 'outbound')
            ->whereBetween('inbound.created_at', [$startDate, $endDate])
            ->whereRaw('outbound.created_at > inbound.created_at')
            ->when($agentId, fn($q) => $q->whereHas('conversation', fn($sq) => $sq->where('assigned_to', $agentId)))
            ->value('seconds');

        return [
            'by_hour' => $byHour,
            'by_type' => $byType,
            'by_direction' => $byDirection,
            'by_status' => $byStatus,
            'by_agent' => $byAgent,
            'top_contacts' => $topContacts,
            'avg_response_time_seconds' => round($avgResponseTime ?? 0),
            'date_range' => ['start' => $startDate, 'end' => $endDate],
        ];
    }

    // GET /reports/conversations
    public function conversations(Request $request)
    {
        $query = Conversation::query()
            ->with(['contact', 'assignedUser', 'messages'])
            ->orderByDesc('last_message_at');

        if ($request->query('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->query('agent_id')) {
            $query->where('assigned_to', $request->query('agent_id'));
        }
        if ($request->query('priority')) {
            $query->where('priority', $request->query('priority'));
        }
        if ($request->query('start_date')) {
            $query->whereBetween('created_at', [
                $request->query('start_date'),
                $request->query('end_date', now()),
            ]);
        }
        if ($request->query('search')) {
            $search = $request->query('search');
            $query->whereHas('contact', fn($q) => 
                $q->where('name', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
            );
        }

        return response()->json($query->paginate(50));
    }

    // GET /reports/export-conversations
    public function exportConversations(Request $request)
    {
        $format = $request->query('format', 'csv');
        
        $conversations = Conversation::with(['contact', 'assignedUser', 'messages'])
            ->when($request->query('status'), fn($q) => $q->where('status', $request->query('status')))
            ->when($request->query('agent_id'), fn($q) => $q->where('assigned_to', $request->query('agent_id')))
            ->orderByDesc('last_message_at')
            ->get();

        if ($format === 'csv') {
            return $this->exportCsv($conversations);
        }
    }

    protected function exportCsv($conversations)
    {
        $csv = fopen('php://memory', 'r+');
        
        fputcsv($csv, [
            'ID', 'Contato', 'Telefone', 'Agente Atribuído', 'Status', 
            'Prioridade', 'Mensagens', 'Última Mensagem', 'Criada em'
        ]);

        foreach ($conversations as $conv) {
            fputcsv($csv, [
                $conv->id,
                $conv->contact->name,
                $conv->contact->phone,
                $conv->assignedUser?->name ?? 'Não atribuído',
                $conv->status,
                $conv->priority,
                $conv->messages->count(),
                $conv->last_message_at?->format('d/m/Y H:i') ?? '-',
                $conv->created_at->format('d/m/Y H:i'),
            ]);
        }

        rewind($csv);
        $csv_contents = stream_get_contents($csv);
        fclose($csv);

        return response($csv_contents, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="conversas_' . date('Y-m-d_His') . '.csv"',
        ]);
    }
}
