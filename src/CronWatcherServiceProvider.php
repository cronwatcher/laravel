<?php

namespace CronWatcher\Laravel;

use CronWatcher\Laravel\Console\Commands\ProfileTask;
use CronWatcher\Laravel\Profiling\Profiler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use CronWatcher\Laravel\Console\Commands\UpdateCronWatcher;

class CronWatcherServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        $this->mergeConfigFrom(
            __DIR__ . '/../config/cronwatcher.php',
            'cronwatcher'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../config/log.php',
            'logging.channels'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(Schedule $schedule, RegisterCallBacks $callBacks, Profiler $profiler): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateCronWatcher::class,
                ProfileTask::class,
            ]);
        }

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('cronwatcher:update')->hourly();
        });

        $this->app->booted(function () use ($schedule, $callBacks, $profiler) {
            $callBacks::register($schedule, $profiler);
        });

    }
}
