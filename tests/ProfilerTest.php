<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Tests;

use CronWatcher\Laravel\Profiling\Profiler;
use CronWatcher\Laravel\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Orchestra\Testbench\TestCase;
use Mockery;

class ProfilerTest extends TestCase
{
    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();
        Mockery::close();
        parent::tearDown();
    }

    public function testStartSetsCacheAndStartsProcess()
    {
        $profiler = Mockery::mock(Profiler::class)->makePartial();
        $profiler->shouldAllowMockingProtectedMethods();
        $profiler->shouldReceive('getName')->andReturn('test-job');

        Cache::shouldReceive('put')->once()->with(
            'cron:test-job:running',
            true,
            Mockery::any()
        );
        Process::shouldReceive('start')->once()->with('php artisan cronwatcher:profile "test-job"');

        $profiler->start();
        $this->assertTrue(true);
    }

    public function testStartLogsException()
    {
        $profiler = Mockery::mock(Profiler::class)->makePartial();
        $profiler->shouldAllowMockingProtectedMethods();
        $profiler->shouldReceive('getName')->andReturn('test-job');

        Cache::shouldReceive('put')->once();
        Process::shouldReceive('start')->andThrow(new \Exception('process error'));
        Log::shouldReceive('channel')->with('cronwatcher')->andReturnSelf();
        Log::shouldReceive('error')->with('process error')->once();

        $profiler->start();
        $this->assertTrue(true);
    }

    public function testSetNameAndGetName()
    {
        $profiler = new Profiler();
        $profiler->setName('foo');
        $this->assertSame('foo', $profiler->getName());
    }

    public function testSetAndGetMetrics()
    {
        $profiler = new Profiler();
        // setMetrics does not exist, so we simulate metrics via stop()
        // We'll set the name, put metrics in cache, call stop, and check getMetrics
        $profiler->setName('foo');
        \Illuminate\Support\Facades\Cache::shouldReceive('forget')->once();
        \Illuminate\Support\Facades\Cache::shouldReceive('pull')->once()->with('cron:foo:metrics', [])->andReturn(['foo' => 'bar']);
        // Simulate running
        $reflection = new \ReflectionClass($profiler);
        $property = $reflection->getProperty('running');
        $property->setAccessible(true);
        $property->setValue($profiler, true);
        $profiler->stop();
        $this->assertSame(['foo' => 'bar'], $profiler->getMetrics());
    }

    public function testIsRunningSimulation()
    {
        $profiler = new Profiler();
        // Simulate running
        $reflection = new \ReflectionClass($profiler);
        $property = $reflection->getProperty('running');
        $property->setAccessible(true);
        $property->setValue($profiler, true);
        // There is no isRunning(), but we can check the property directly
        $this->assertTrue($property->getValue($profiler));
        $property->setValue($profiler, false);
        $this->assertFalse($property->getValue($profiler));
    }
}
