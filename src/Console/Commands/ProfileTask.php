<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Console\Commands;

use CronWatcher\Laravel\Profiling\DatabaseProfiler;
use CronWatcher\Laravel\Profiling\SystemProfiler;
use CronWatcher\Laravel\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ProfileTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronwatcher:profile {jobName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It profiles database and system metrics for a given job';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseProfiler $databaseProfiler, SystemProfiler $systemProfiler): int
    {
        $jobName = $this->argument('jobName');
        $databaseProfiler->listen();

        $maxSeconds = Settings::PROFILING_MAX_EXECUTE_SECONDS;
        $start      = time();

        while (Cache::get("cron:{$jobName}:running")) {
            if ((time() - $start) > $maxSeconds) {
                Log::channel('cronwatcher')->warning("Profiling timeout: job [{$jobName}] exceeded {$maxSeconds} seconds.");
                break;
            }
            $cpuLoad = $systemProfiler->getCpuLoad();
            $entry   = [
                'timestamp'           => now()->toDateTimeString(),
                'memory_mb'           => $systemProfiler->getMemoryUsage(),
                'cpu_1min'            => $cpuLoad[0] ?? null,
                'cpu_5min'            => $cpuLoad[1] ?? null,
                'query_count'         => count($databaseProfiler->getQueries()),
                'total_query_time_ms' => $databaseProfiler->getTotalQueryTime(),
                'active_connections'  => $databaseProfiler->getActiveConnections(),
            ];

            $metrics   = Cache::get("cron:{$jobName}:metrics", []);
            $metrics[] = $entry;
            try {
                Cache::put("cron:{$jobName}:metrics", $metrics, now()->addHours(Settings::PROFILING_MAX_EXECUTE_SECONDS));
            } catch (\Exception $e) {
                Log::channel('cronwatcher')->error($e->getMessage());
            }
            sleep(Settings::PROFILING_INTERVAL_SECONDS);
        }

        return CommandAlias::SUCCESS;
    }
}
