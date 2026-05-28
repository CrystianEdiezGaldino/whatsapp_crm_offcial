<?php

use Illuminate\Support\Facades\Route;
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
    Route::resource('agents', \App\Http\Controllers\Admin\AgentController::class);
    Route::post('/agents/{user}/reset-password', [\App\Http\Controllers\Admin\AgentController::class, 'resetPassword'])->name('agents.reset-password');
    Route::patch('/agents/{user}/toggle-active', [\App\Http\Controllers\Admin\AgentController::class, 'toggleActive'])->name('agents.toggle-active');
    Route::get('/agents/export/csv', [\App\Http\Controllers\Admin\AgentController::class, 'export'])->name('agents.export');

    // Distribution
    Route::get('/distribution', [\App\Http\Controllers\Admin\DistributionController::class, 'index'])->name('distribution.index');
    Route::post('/distribution/settings', [\App\Http\Controllers\Admin\DistributionController::class, 'updateSettings'])->name('distribution.settings');
    Route::patch('/distribution/agents/{user}/capacity', [\App\Http\Controllers\Admin\DistributionController::class, 'updateAgentCapacity'])->name('distribution.agent.capacity');
    Route::post('/distribution/process-queue', [\App\Http\Controllers\Admin\DistributionController::class, 'processQueue'])->name('distribution.process-queue');
    Route::get('/distribution/metrics', [\App\Http\Controllers\Admin\DistributionController::class, 'metrics'])->name('distribution.metrics');

    // Tags
    Route::resource('tags', \App\Http\Controllers\Admin\TagController::class);
    Route::patch('/tags/{tag}/toggle-active', [\App\Http\Controllers\Admin\TagController::class, 'toggleActive'])->name('tags.toggle-active');

    // Complaints (rotas fixas antes do resource para não conflitar com {complaint})
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

    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
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
    Route::post('/conversations/{conversation}/tags', [TagController::class, 'attachToConversation'])->name('conversations.tags.attach');
    Route::delete('/conversations/{conversation}/tags/{tag}', [TagController::class, 'detachFromConversation'])->name('conversations.tags.detach');

    Route::post('/conversations/reopen/request', [\App\Http\Controllers\ConversationReopenController::class, 'request'])->name('conversations.reopen.request');
    Route::get('/conversations/reopen/pending', [\App\Http\Controllers\ConversationReopenController::class, 'pending'])->name('conversations.reopen.pending');
    Route::post('/conversations/reopen/{reopenRequest}/approve', [\App\Http\Controllers\ConversationReopenController::class, 'approve'])->name('conversations.reopen.approve');
    Route::post('/conversations/reopen/{reopenRequest}/reject', [\App\Http\Controllers\ConversationReopenController::class, 'reject'])->name('conversations.reopen.reject');
    Route::get('/audit/conversations', [AuditController::class, 'conversation'])->name('audit.conversations');
    Route::get('/audit/activity', [AuditController::class, 'agentActivity'])->name('audit.activity');

    Route::resource('macros', MacroController::class);
    Route::post('/macros/{macro}/files', [MacroFileController::class, 'store'])->name('macros.files.store');
    Route::delete('/macros/{macro}/files/{file}', [MacroFileController::class, 'destroy'])->name('macros.files.destroy');
    Route::patch('/macros/{macro}/files/{file}/reorder', [MacroFileController::class, 'reorder'])->name('macros.files.reorder');
    Route::get('/macros/{macro}/preview', [MacroFileController::class, 'preview'])->name('macros.preview');

    Route::prefix('reports')->group(function () {
        Route::get('/dashboard-data', [ReportController::class, 'dashboardData'])->name('reports.dashboard-data');
        Route::get('/conversations', [ReportController::class, 'conversations'])->name('reports.conversations');
        Route::get('/export-conversations', [ReportController::class, 'exportConversations'])->name('reports.export-conversations');
    });
});

Route::get('/docs', [DocumentationController::class, 'index'])->name('documentation.index');
Route::get('/docs/components/{component}', [DocumentationController::class, 'component'])->name('documentation.component');

// API endpoints para modal de transferência
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

// Webhook Debug (apenas em desenvolvimento)
Route::get('/webhook/debug', [WebhookController::class, 'debug'])->name('webhook.debug');
Route::post('/webhook/debug', [WebhookController::class, 'debug']);

// Termos e Privacidade
Route::get('/termos-privacidade', function () {
    return file_get_contents(public_path('termos-privacidade.html'));
})->name('termos-privacidade');

Route::get('/', function () {
    return redirect()->route('dashboard');
});
