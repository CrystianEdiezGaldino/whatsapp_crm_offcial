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

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Admin Routes
Route::middleware(['auth', 'ensure_is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('agents', \App\Http\Controllers\Admin\AgentController::class);

    Route::get('/distribution', [\App\Http\Controllers\Admin\DistributionController::class, 'index'])->name('distribution.index');
    Route::post('/distribution/settings', [\App\Http\Controllers\Admin\DistributionController::class, 'updateSettings'])->name('distribution.settings');
    Route::patch('/distribution/agents/{user}/capacity', [\App\Http\Controllers\Admin\DistributionController::class, 'updateAgentCapacity'])->name('distribution.agent.capacity');
    Route::get('/distribution/metrics', [\App\Http\Controllers\Admin\DistributionController::class, 'metrics'])->name('distribution.metrics');
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
    Route::get('/conversations/{conversation}/history', [ConversationController::class, 'history'])->name('conversations.history');
    Route::get('/conversations/{conversation}/history-view', [ConversationController::class, 'showHistoryConversation'])->name('conversations.history-view');

    Route::post('/conversations/{conversation}/claim', [ConversationClaimController::class, 'claim'])->name('conversations.claim');
    Route::delete('/conversations/{conversation}/claim', [ConversationClaimController::class, 'release'])->name('conversations.release');
    Route::patch('/conversations/{conversation}/reassign', [ConversationClaimController::class, 'reassign'])->name('conversations.reassign');
    Route::get('/conversations/{conversation}/claim-history', [ConversationClaimController::class, 'history'])->name('conversations.claim-history');

    Route::get('/conversations/{conversation}/audit/timeline', [AuditController::class, 'timeline'])->name('audit.timeline');
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

Route::get('/', function () {
    return redirect()->route('dashboard');
});
