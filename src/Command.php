<?php

declare(strict_types=1);

namespace CronWatcher\Laravel;

class Command
{
    public static function getCommandDescription(?string $commandName, $commands): string
    {
        if (isset($commands[$commandName]) && $commands[$commandName] instanceof \Illuminate\Console\Command) {
            return $commands[$commandName]->getDescription();
        }

        return '';
    }
}
