<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogSlowQueries
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        $queryCount = 0;
        $totalQueryTime = 0;

        DB::listen(function ($query) use (&$queryCount, &$totalQueryTime) {
            $queryCount++;
            $totalQueryTime += $query->time;

            if ($query->time > 100) {
                Log::warning('Slow Query', [
                    'time' => $query->time . 'ms',
                    'query' => substr($query->sql, 0, 150),
                ]);
            }
        });

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;
        if ($duration > 1000) { // Log requests slower than 1 second
            Log::warning('Slow Request', [
                'path' => $request->path(),
                'duration_ms' => round($duration, 2),
                'query_count' => $queryCount,
                'total_query_time_ms' => round($totalQueryTime, 2),
                'php_time_ms' => round($duration - $totalQueryTime, 2),
            ]);
        }

        return $response;
    }
}
