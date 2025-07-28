<?php

namespace CronWatcher\Laravel;

final class Settings
{

    public const PROFILING_MAX_EXECUTE_SECONDS = 600;
    public const PROFILING_INTERVAL_SECONDS = 5;
    public static function getUrl(): string
    {
        return 'http://host.docker.internal:82/api';
    }

    public static function getToken()
    {
        return config('cronwatcher.key');
    }

    public static function getTimezone()
    {
        if (config('app.schedule_timezone')) {
            return config('app.schedule_timezone');
        }
        return config('app.timezone');
    }

}
