<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ConversationClaimController;
use App\Http\Controllers\MacroController;
use App\Http\Controllers\MacroFileController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\Admin\FlowController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Admin Routes
Route::middleware(['auth', 'ensure_is_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Sectors
    Route::resource('sectors', \App\Http\Controllers\Admin\SectorController::class);
    Route::patch('/sectors/{sector}/toggle-active', [\App\Http\Controllers\Admin\SectorController::class, 'toggleActive'])->name('sectors.toggle-active');

    // Agents
    Route::resource('agents', \App\Http\Controllers\Admin\AgentController::class)
        ->parameters(['agents' => 'user']);
    Route::post('/agents/{user}/reset-password', [\App\Http\Controllers\Admin\AgentController::class, 'resetPassword'])->name('agents.reset-password');
    Route::patch('/agents/{user}/toggle-active', [\App\Http\Controllers\Admin\AgentController::class, 'toggleActive'])->name('agents.toggle-active');
    Route::get('/agents/export/csv', [\App\Http\Controllers\Admin\AgentController::class, 'export'])->name('agents.export');

    // Distribution
    Route::get('/distribution', [\App\Http\Controllers\Admin\DistributionController::class, 'index'])->name('distribution.index');
    Route::post('/distribution/settings', [\App\Http\Controllers\Admin\DistributionController::class, 'updateSettings'])->name('distribution.settings');
    Route::patch('/distribution/agents/{user}/capacity', [\App\Http\Controllers\Admin\DistributionController::class, 'updateAgentCapacity'])->name('distribution.agent.capacity');
    Route::post('/distribution/process-queue', [\App\Http\Controllers\Admin\DistributionController::class, 'processQueue'])->name('distribution.process-queue');
    Route::get('/distribution/metrics', [\App\Http\Controllers\Admin\DistributionController::class, 'metrics'])->name('distribution.metrics');

    // Conversation Flows
    Route::resource('flows', FlowController::class)->except(['show']);
    Route::post('/flows/{flow}/toggle', [FlowController::class, 'toggle'])->name('flows.toggle');
    Route::get('/flows/{flow}/executions', [FlowController::class, 'executions'])->name('flows.executions');
    Route::get('/flows/validate-variables', [FlowController::class, 'validateVariables'])->name('flows.validate-variables');
    Route::get('/flows/preview-variables', [FlowController::class, 'previewVariables'])->name('flows.preview-variables');
    Route::get('/flows/available-variables', [FlowController::class, 'availableVariables'])->name('flows.available-variables');

    // Tags
    Route::resource('tags', \App\Http\Controllers\Admin\TagController::class);
    Route::patch('/tags/{tag}/toggle-active', [\App\Http\Controllers\Admin\TagController::class, 'toggleActive'])->name('tags.toggle-active');

    // Complaints (rotas fixas antes do resource para n??o conflitar com {complaint})
    Route::get('/complaints/dashboard', [\App\Http\Controllers\Admin\ComplaintController::class, 'dashboard'])->name('complaints.dashboard');
    Route::resource('complaints', \App\Http\Controllers\Admin\ComplaintController::class, ['only' => ['index', 'show']]);
    Route::get('/complaints/{complaint}/review', [\App\Http\Controllers\Admin\ComplaintController::class, 'review'])->name('complaints.review');
    Route::post('/complaints/{complaint}/resolve', [\App\Http\Controllers\Admin\ComplaintController::class, 'resolve'])->name('complaints.resolve');
    Route::post('/complaints/{complaint}/dismiss', [\App\Http\Controllers\Admin\ComplaintController::class, 'dismiss'])->name('complaints.dismiss');

    // Transfers (rotas fixas antes do resource)
    Route::get('/transfers/pending', [\App\Http\Controllers\Admin\TransferController::class, 'pending'])->name('transfers.pending');
    Route::get('/transfers/analytics', [\App\Http\Controllers\Admin\TransferController::class, 'analytics'])->name('transfers.analytics');
    Route::resource('transfers', \App\Http\Controllers\Admin\TransferController::class, ['only' => ['index', 'show']]);
    Route::post('/transfers/{transfer}/approve', [\App\Http\Controllers\Admin\TransferController::class, 'approve'])->name('transfers.approve');
    Route::post('/transfers/{transfer}/reject', [\App\Http\Controllers\Admin\TransferController::class, 'reject'])->name('transfers.reject');
    Route::post('/transfers/{transfer}/complete', [\App\Http\Controllers\Admin\TransferController::class, 'complete'])->name('transfers.complete');

    // SLA
    Route::get('/sla', [\App\Http\Controllers\Admin\SLAController::class, 'dashboard'])->name('sla.dashboard');
    Route::get('/sla/metrics', [\App\Http\Controllers\Admin\SLAController::class, 'metrics'])->name('sla.metrics');
    Route::post('/sla/check-breaches', [\App\Http\Controllers\Admin\SLAController::class, 'checkBreaches'])->name('sla.check-breaches');

    // WhatsApp Token Management
    Route::get('/whatsapp/tokens', [\App\Http\Controllers\Admin\WhatsAppTokenController::class, 'index'])->name('whatsapp.token.index');
    Route::post('/whatsapp/tokens/refresh', [\App\Http\Controllers\Admin\WhatsAppTokenController::class, 'refresh'])->name('whatsapp.token.refresh');
    Route::post('/whatsapp/tokens/store', [\App\Http\Controllers\Admin\WhatsAppTokenController::class, 'storeManual'])->name('whatsapp.token.store');
    Route::post('/whatsapp/tokens/sync-env', [\App\Http\Controllers\Admin\WhatsAppTokenController::class, 'syncFromEnv'])->name('whatsapp.token.sync-env');

    // WhatsApp Numbers Management
    Route::get('/whatsapp/numbers', [\App\Http\Controllers\Admin\WhatsAppNumberController::class, 'index'])->name('whatsapp.numbers.index');
    Route::get('/whatsapp/numbers/create', [\App\Http\Controllers\Admin\WhatsAppNumberController::class, 'create'])->name('whatsapp.numbers.create');
    Route::post('/whatsapp/numbers', [\App\Http\Controllers\Admin\WhatsAppNumberController::class, 'store'])->name('whatsapp.numbers.store');
    Route::post('/whatsapp/numbers/sync-meta', [\App\Http\Controllers\Admin\WhatsAppNumberController::class, 'syncFromMeta'])->name('whatsapp.numbers.sync-meta');
    Route::post('/whatsapp/numbers/{whatsAppNumber}/set-active', [\App\Http\Controllers\Admin\WhatsAppNumberController::class, 'setActive'])->name('whatsapp.numbers.set-active');
    Route::post('/whatsapp/numbers/{whatsAppNumber}/verify', [\App\Http\Controllers\Admin\WhatsAppNumberController::class, 'verify'])->name('whatsapp.numbers.verify');
    Route::delete('/whatsapp/numbers/{whatsAppNumber}', [\App\Http\Controllers\Admin\WhatsAppNumberController::class, 'destroy'])->name('whatsapp.numbers.destroy');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('contacts', ContactController::class);
    Route::patch('/contacts/{contact}/notes', [ContactController::class, 'updateNotes'])->name('contacts.notes.update');

    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/list-poll', [ConversationController::class, 'pollList'])->name('conversations.list-poll');
    Route::get('/conversations/macros-json', [ConversationController::class, 'macrosJson'])->name('conversations.macros-json');
    Route::post('/conversations/send', [ConversationController::class, 'sendMessage'])->name('conversations.send');
    Route::get('/conversations/{conversation}/poll', [ConversationController::class, 'poll'])->name('conversations.poll');
    Route::post('/conversations/start', [ConversationController::class, 'startConversation'])->name('conversations.start');
    Route::patch('/conversations/{conversation}/assign', [ConversationController::class, 'assign'])->name('conversations.assign');
    Route::patch('/conversations/{conversation}/resolve', [ConversationController::class, 'resolve'])->name('conversations.resolve');
    Route::post('/conversations/resolve-with-reason', [ConversationController::class, 'resolveWithReason'])->name('conversations.resolve-with-reason');
    Route::get('/conversations/{conversation}/history', [ConversationController::class, 'history'])->name('conversations.history');
    Route::get('/conversations/{conversation}/history-view', [ConversationController::class, 'showHistoryConversation'])->name('conversations.history-view');

    Route::post('/conversations/{conversation}/claim', [ConversationClaimController::class, 'claim'])->name('conversations.claim');
    Route::delete('/conversations/{conversation}/claim', [ConversationClaimController::class, 'release'])->name('conversations.release');
    Route::patch('/conversations/{conversation}/reassign', [ConversationClaimController::class, 'reassign'])->name('conversations.reassign');
    Route::get('/conversations/{conversation}/claim-history', [ConversationClaimController::class, 'history'])->name('conversations.claim-history');

    Route::get('/conversations/{conversation}/audit/timeline', [AuditController::class, 'timeline'])->name('audit.timeline');

    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::get('/conversations/{conversation}/tags-json', [TagController::class, 'conversationTags'])->name('conversations.tags.json');
    Route::post('/conversations/{conversation}/tags', [TagController::class, 'attachToConversation'])->name('conversations.tags.attach');
    Route::delete('/conversations/{conversation}/tags/{tag}', [TagController::class, 'detachFromConversation'])->name('conversations.tags.detach');

    Route::get('/sectors-json', [SectorController::class, 'index'])->name('sectors.index');
    Route::get('/conversations/{conversation}/sector-json', [SectorController::class, 'conversationSector'])->name('conversations.sector.json');
    Route::patch('/conversations/{conversation}/sector', [SectorController::class, 'updateConversationSector'])->name('conversations.sector.update');

    Route::post('/conversations/reopen/request', [\App\Http\Controllers\ConversationReopenController::class, 'request'])->name('conversations.reopen.request');
    Route::get('/conversations/reopen/pending', [\App\Http\Controllers\ConversationReopenController::class, 'pending'])->name('conversations.reopen.pending');
    Route::post('/conversations/reopen/{reopenRequest}/approve', [\App\Http\Controllers\ConversationReopenController::class, 'approve'])->name('conversations.reopen.approve');
    Route::post('/conversations/reopen/{reopenRequest}/reject', [\App\Http\Controllers\ConversationReopenController::class, 'reject'])->name('conversations.reopen.reject');
    Route::get('/audit/conversations', [AuditController::class, 'conversation'])->name('audit.conversations');
    Route::get('/audit/activity', [AuditController::class, 'agentActivity'])->name('audit.activity');

    // Text improvement
    Route::post('/conversations/{conversation}/improve-text', [ConversationController::class, 'improveText'])
        ->name('conversations.improve-text');

    Route::post('/macros/improve-text', [MacroController::class, 'improveText'])->name('macros.improve-text');
    Route::resource('macros', MacroController::class);
    Route::post('/macros/{macro}/files', [MacroFileController::class, 'store'])->name('macros.files.store');
    Route::delete('/macros/{macro}/files/{file}', [MacroFileController::class, 'destroy'])->name('macros.files.destroy');
    Route::patch('/macros/{macro}/files/{file}/reorder', [MacroFileController::class, 'reorder'])->name('macros.files.reorder');
    Route::get('/macros/{macro}/preview', [MacroFileController::class, 'preview'])->name('macros.preview');

    // Debug endpoint for macros and emojis
    Route::get('/debug/macros', function() {
        $macros = \Illuminate\Support\Facades\Cache::remember(
            'macros_' . \Illuminate\Support\Facades\Auth::id(),
            3600,
            fn() => \App\Models\Macro::where('user_id', \Illuminate\Support\Facades\Auth::id())->orWhereNull('user_id')->get()
        );
        return response()->json([
            'success' => true,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'macro_count' => $macros->count(),
            'macros' => $macros->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'shortcut' => $m->shortcut,
                'content' => substr($m->content, 0, 100) . '...',
                'user_id' => $m->user_id,
            ]),
        ]);
    })->name('debug.macros');

    Route::prefix('reports')->group(function () {
        Route::get('/dashboard-data', [ReportController::class, 'dashboardData'])->name('reports.dashboard-data');
        Route::get('/conversations', [ReportController::class, 'conversations'])->name('reports.conversations');
        Route::get('/export-conversations', [ReportController::class, 'exportConversations'])->name('reports.export-conversations');
    });
});

Route::get('/docs', [DocumentationController::class, 'index'])->name('documentation.index');
Route::get('/docs/components/{component}', [DocumentationController::class, 'component'])->name('documentation.component');

// API endpoints para modal de transfer??ncia
Route::middleware('auth')->get('/api/agents', function () {
    $agents = \App\Models\User::select('id', 'name', 'email', 'role')
        ->orderBy('name')
        ->get();

    return response()->json([
        'success' => true,
        'agents' => $agents,
    ]);
});

// Health Check Routes
Route::get('/health', [HealthController::class, 'status'])->name('health.status');
Route::get('/health/api', [HealthController::class, 'api'])->name('health.api');
Route::middleware('auth')->group(function () {
    Route::get('/health/dashboard', [HealthController::class, 'dashboard'])->name('health.dashboard');
    Route::get('/health/webhooks', [HealthController::class, 'webhookLogs'])->name('health.webhooks');
});

// WhatsApp Webhook Routes
Route::get('/webhook/whatsapp', [WebhookController::class, 'verify'])->name('webhook.verify');
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle'])->name('webhook.handle');

// WhatsApp Webhook Routes (com /api prefix)
Route::get('/api/webhook/whatsapp', [WebhookController::class, 'verify']);
Route::post('/api/webhook/whatsapp', [WebhookController::class, 'handle']);

// Webhook Debug (apenas em desenvolvimento)
Route::get('/webhook/debug', [WebhookController::class, 'debug'])->name('webhook.debug');
Route::post('/webhook/debug', [WebhookController::class, 'debug']);

// Termos e Privacidade
Route::get('/termos-privacidade', function () {
    return file_get_contents(public_path('termos-privacidade.html'));
})->name('termos-privacidade');

// Test SQL Server connection to a specific host
Route::get('/test-connect', function (Request $request) {
    $host = $request->query('host', '192.168.1.6');
    $port = (int) $request->query('port', 1433);
    $user = $request->query('user', 'Php');
    $pass = $request->query('pass', '$89%3a7');
    $db = $request->query('db', 'Whatsapp');

    $results = ['host' => $host, 'port' => $port];

    // Test socket
    $socket = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($socket) {
        fclose($socket);
        $results['socket'] = ['status' => 'OK'];

        // Try PDO connection
        try {
            $dsn = "sqlsrv:Server=$host,$port;Database=$db;Encrypt=optional;TrustServerCertificate=true";
            $pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $results['status'] = 'OK';
            $results['message'] = "??? Connected to SQL Server at $host:$port";

            $stmt = $pdo->query('SELECT COUNT(*) as table_count FROM information_schema.TABLES WHERE TABLE_CATALOG = ?', [\PDO::FETCH_ASSOC]);
            $count = $stmt->fetchColumn();
            $results['tables'] = $count;

        } catch (\Exception $e) {
            $results['status'] = 'FAILED';
            $results['error'] = $e->getMessage();
        }
    } else {
        $results['status'] = 'FAILED';
        $results['socket'] = ['status' => 'FAILED', 'error' => $errstr];
    }

    return response()->json($results);
});

// Database Diagnostic Route
Route::get('/diagnose/db', function () {
    $host = '192.168.1.6';
    $port = 1433;
    $user = 'Php';
    $pass = '$89%3a7';
    $db = 'Whatsapp';

    $results = [
        'timestamp' => now()->toIso8601String(),
        'host' => $host,
        'port' => $port,
        'database' => $db,
    ];

    // 1. Test Socket Connection
    $socket = @fsockopen($host, $port, $errno, $errstr, 5);
    if ($socket) {
        fclose($socket);
        $results['socket'] = ['status' => 'OK', 'message' => 'Port 1433 is reachable'];
    } else {
        $results['socket'] = ['status' => 'FAILED', 'error' => $errstr, 'error_code' => $errno];
    }

    // 2. Test PHP Extensions
    $results['php_extensions'] = [
        'sqlsrv' => extension_loaded('sqlsrv') ? 'INSTALLED' : 'MISSING',
        'pdo_sqlsrv' => extension_loaded('pdo_sqlsrv') ? 'INSTALLED' : 'MISSING',
    ];

    // 3. Test PDO Connection (if driver installed)
    if (extension_loaded('pdo_sqlsrv')) {
        try {
            $dsn = "sqlsrv:Server=$host,$port;Database=$db;Encrypt=no;TrustServerCertificate=false";
            $pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $results['pdo'] = ['status' => 'OK', 'message' => 'Connected to SQL Server'];

            // Try a simple query
            $stmt = $pdo->query('SELECT 1');
            $results['query'] = ['status' => 'OK', 'message' => 'Query executed successfully'];

        } catch (\PDOException $e) {
            $results['pdo'] = ['status' => 'FAILED', 'error' => $e->getMessage()];
            $results['query'] = ['status' => 'SKIPPED'];
        }
    } else {
        $results['pdo'] = ['status' => 'SKIPPED', 'reason' => 'pdo_sqlsrv driver not installed'];
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

Route::get('/', function () {
    return redirect()->route('login');
});

