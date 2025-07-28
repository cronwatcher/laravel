<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Tests;

use CronWatcher\Laravel\Profiling\DatabaseProfiler;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;
use Mockery;

class DatabaseProfilerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCheckDBConnectionReturnsTrueWhenPdoExists()
    {
        $pdoMock = Mockery::mock();
        DB::shouldReceive('connection')->once()->andReturnSelf();
        DB::shouldReceive('getPdo')->once()->andReturn($pdoMock);
        $profiler = new DatabaseProfiler();
        $this->assertTrue($profiler->checkDBConnection());
    }

    public function testCheckDBConnectionReturnsFalseWhenPdoIsNull()
    {
        DB::shouldReceive('connection')->once()->andReturnSelf();
        DB::shouldReceive('getPdo')->once()->andReturn(null);
        $profiler = new DatabaseProfiler();
        $this->assertFalse($profiler->checkDBConnection());
    }

    public function testListenDoesNothingIfNoConnection()
    {
        $profiler = Mockery::mock(DatabaseProfiler::class)->makePartial();
        $profiler->shouldReceive('checkDBConnection')->once()->andReturn(false);
        DB::shouldReceive('listen')->never();
        $profiler->listen();
        $this->assertTrue(true);
    }

    public function testQueryCollectionAndReset()
    {
        $profiler = new DatabaseProfiler();
        // Simulate listen() by manually adding queries
        $reflection = new \ReflectionClass($profiler);
        $property = $reflection->getProperty('queries');
        $property->setAccessible(true);
        $property->setValue($profiler, [
            ['sql' => 'select * from users', 'bindings' => [], 'time' => 10],
            ['sql' => 'insert into users (name) values (?)', 'bindings' => ['foo'], 'time' => 5],
        ]);

        $queries = $property->getValue($profiler);
        $this->assertCount(2, $queries);
        $this->assertSame('select * from users', $queries[0]['sql']);

        // Reset and check
        $profiler->reset();
        $this->assertEmpty($property->getValue($profiler));
    }
}
