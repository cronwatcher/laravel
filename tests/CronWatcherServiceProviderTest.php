<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Tests;

use CronWatcher\Laravel\CronWatcherServiceProvider;
use CronWatcher\Laravel\Profiling\Profiler;
use CronWatcher\Laravel\RegisterCallBacks;
use Illuminate\Console\Scheduling\Schedule;
use Orchestra\Testbench\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class CronWatcherServiceProviderTest extends TestCase
{
    public function testBootRegistersScheduleAndCallbacks()
    {
        $schedule  = \Mockery::mock(Schedule::class);
        $callBacks = \Mockery::mock(RegisterCallBacks::class);
        $profiler  = \Mockery::mock(Profiler::class);

        $callBacks->shouldReceive('register')->once()->with($schedule, $profiler);
        $schedule->shouldReceive('command')->with('cronwatcher:update')->andReturnSelf();
        $schedule->shouldReceive('hourly')->andReturnSelf();

        $provider = new CronWatcherServiceProvider($this->app);
        $this->app->instance(Schedule::class, $schedule);
        $this->app->instance(RegisterCallBacks::class, $callBacks);
        $this->app->instance(Profiler::class, $profiler);

        $provider->boot($schedule, $callBacks, $profiler);
        $this->assertTrue(true); // If no exception, test passes
    }

    protected function getPackageProviders($app): array
    {
        return [CronWatcherServiceProvider::class];
    }
}
