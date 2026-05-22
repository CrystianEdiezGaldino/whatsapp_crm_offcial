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

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
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

Route::get('/', function () {
    return redirect()->route('dashboard');
});
