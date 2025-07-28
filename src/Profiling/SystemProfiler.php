<?php

namespace CronWatcher\Laravel\Profiling;

class SystemProfiler
{
    public function getMemoryUsage(): ?float
    {
        return function_exists('memory_get_usage')
          ? round(memory_get_usage(true) / 1048576, 2)
          : null;
    }

    public function getCpuLoad(): ?array
    {
        return function_exists('sys_getloadavg')
          ? sys_getloadavg()
          : [null, null, null];
    }

}
