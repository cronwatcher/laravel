<?php

namespace CronWatcher\Laravel\Profiling;

use Illuminate\Support\Facades\DB;

class DatabaseProfiler
{
    protected array $queries = [];
    protected float $totalQueryTime = 0;
    public function checkDBConnection(): bool
    {
        return (bool) DB::connection()->getPdo();
    }

    public function listen(): void
    {
        if ($this->checkDBConnection() === false) {
            return;
        }

        DB::listen(function ($query) use (&$queries, &$totalQueryTime) {
            $this->reset();
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time_ms' => $query->time,
              ];
            $this->totalQueryTime += $query->time;
        });
    }

    public function getActiveConnections(): int
    {
        if ($this->checkDBConnection() === false) {
            return 0;
        }

        try {
            $result = DB::select("SHOW STATUS WHERE `variable_name` = 'Threads_connected'");
            if (!empty($result)) {

                return $result[0]->Value;
            }
        } catch (\Exception) {
            return 0;
        }
        return 0;
    }
    public function getQueries(): array
    {
        return $this->queries;
    }

    public function getTotalQueryTime(): float
    {
        return $this->totalQueryTime;
    }

    public function reset(): void
    {
        $this->queries = [];
        $this->totalQueryTime = 0;
        DB::flushQueryLog();
    }
}
