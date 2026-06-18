<?php

namespace App\Providers;

use App\Database\Grammars\SqlServerGrammar;
use App\Database\Grammars\SqlServerQueryGrammar;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Macro;
use App\Observers\ConversationObserver;
use App\Observers\MessageObserver;
use App\Observers\MacroObserver;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if ($appUrl = config('app.url')) {
            URL::forceRootUrl(rtrim($appUrl, '/'));
        }

        if (config('database.default') === 'sqlsrv') {
            $connection = DB::connection('sqlsrv');
            $connection->setSchemaGrammar(new SqlServerGrammar);
            $connection->setQueryGrammar(new SqlServerQueryGrammar);

            // DATEFORMAT só na 1ª query — evita timeout de 60s em páginas sem DB (ex: login)
            static $dateFormatSet = false;
            DB::listen(function () use (&$dateFormatSet, $connection) {
                if ($dateFormatSet) {
                    return;
                }
                $dateFormatSet = true;
                try {
                    $connection->statement('SET DATEFORMAT ymd');
                } catch (\Exception $e) {
                    Log::warning('Failed to set DATEFORMAT on SQL Server: ' . $e->getMessage());
                }
            });
        }

        // Register Model Observers for Audit Logging
        Conversation::observe(ConversationObserver::class);
        Message::observe(MessageObserver::class);
        Macro::observe(MacroObserver::class);

        // Query Logging - detectar queries lentas
        DB::listen(function (QueryExecuted $query) {
            if ($query->time > 500) { // mais de 500ms
                Log::warning('[Slow Query] ' . round($query->time) . 'ms', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                ]);
            }
        });
    }
}
