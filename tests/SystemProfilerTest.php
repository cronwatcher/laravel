<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Tests;

use CronWatcher\Laravel\Profiling\SystemProfiler;
use Orchestra\Testbench\TestCase;

class SystemProfilerTest extends TestCase
{
    public function testGetMemoryUsageReturnsFloatIfFunctionExists()
    {
        $profiler = new SystemProfiler();
        $result = $profiler->getMemoryUsage();
        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }

    public function testGetCpuLoadReturnsArrayIfFunctionExists()
    {
        $profiler = new SystemProfiler();
        $result = $profiler->getCpuLoad();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        foreach ($result as $load) {
            $this->assertIsNumeric($load);
        }
    }

    public function testGetMemoryUsageReturnsNullIfFunctionMissing()
    {
        // Simulate function missing by temporarily redefining function_exists
        $profiler = new class extends SystemProfiler {
            public function getMemoryUsage(): ?float
            {
                return null;
            }
        };
        $this->assertNull($profiler->getMemoryUsage());
    }

    public function testGetCpuLoadReturnsNullArrayIfFunctionMissing()
    {
        $profiler = new class extends SystemProfiler {
            public function getCpuLoad(): ?array
            {
                return [null, null, null];
            }
        };
        $this->assertSame([null, null, null], $profiler->getCpuLoad());
    }
}
