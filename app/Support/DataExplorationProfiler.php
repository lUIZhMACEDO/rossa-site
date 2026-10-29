<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataExplorationProfiler
{
    public function enabled(): bool
    {
        return (bool) env('DATA_EXPLORATION_PROFILING', false);
    }

    /**
     * Profile one server-side block with query count/time and wall-clock timing.
     */
    public function profile(string $event, array $meta, callable $callback): mixed
    {
        if (! $this->enabled()) {
            return $callback();
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $startedAt = microtime(true);
        $succeeded = false;
        $exceptionClass = null;

        try {
            $result = $callback();
            $succeeded = true;

            return $result;
        } catch (\Throwable $e) {
            $exceptionClass = $e::class;
            throw $e;
        } finally {
            $durationMs = (microtime(true) - $startedAt) * 1000;
            $queries = DB::getQueryLog();
            $queryCount = count($queries);
            $queryTimeMs = array_sum(array_map(fn ($query) => (float) ($query['time'] ?? 0), $queries));

            Log::channel('stats')->info('DE_PERF', array_merge($meta, [
                'event' => $event,
                'duration_ms' => round($durationMs, 2),
                'query_count' => $queryCount,
                'query_time_ms' => round($queryTimeMs, 2),
                'succeeded' => $succeeded,
                'exception' => $exceptionClass,
                'path' => request()?->path(),
                'component' => 'DataExploration',
            ]));

            DB::flushQueryLog();
        }
    }
}
