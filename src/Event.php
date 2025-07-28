<?php

namespace CronWatcher\Laravel;

class Event
{
    public static function getCommand(\Illuminate\Console\Scheduling\Event $event): ?string
    {
        if (is_null($event->command)) {
            return $event->description;
        }

        $commandName = $event->command;

        if (!str_contains($commandName, 'artisan')) {
            return $commandName;
        }
        $parts = explode('artisan', $commandName, 2);

        return isset($parts[1]) ? trim($parts[1], " \t\n\r\0\x0B'\"") : '';
    }
}
