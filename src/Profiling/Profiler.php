<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Profiling;

use CronWatcher\Laravel\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class Profiler
{
    private mixed $metrics = [];
    private string $name;
    private bool $running = false;

    public function start(): void
    {
        $name = $this->getName();
        try {
            Cache::put("cron:{$name}:running", true, now()->addHours(Settings::PROFILING_MAX_EXECUTE_SECONDS));
            Process::start("php artisan cronwatcher:profile \"{$name}\"");
            $this->running = true;
        } catch (\Exception $e) {
            Log::channel('cronwatcher')->error($e->getMessage());
        }
    }

    public function stop(): void
    {
        if (false === $this->running) {
            return;
        }
        $name = $this->getName();

        Cache::forget("cron:{$name}:running");
        $this->metrics = Cache::pull("cron:{$name}:metrics", []);
        $this->running = false;
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
