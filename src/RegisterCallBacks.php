<?php

namespace CronWatcher\Laravel;

use CronWatcher\Laravel\Profiling\Profiler;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

class RegisterCallBacks
{
    public static function register(Schedule $schedule, Profiler $profiler): void
    {
        $commands = Artisan::all();
        $timeStamp = time();

        foreach ($schedule->events() as $event) {
            $commandName = Event::getCommand($event);
            if (is_null($commandName)) {
                continue;
            }
            if ($event->description) {
                $description = $event->description;
            } else {
                $description = Command::getCommandDescription($commandName, $commands);
            }

            $pingParams = [
              'expression' => $event->getExpression(),
              'command' => $commandName,
              'description' => $description,
              'timestamp' => $timeStamp,
            ];

            $logFile = storage_path('logs/schedule-'.sha1($event->mutexName()).'.log');
            $profileName = sha1($event->mutexName());
            $profiler->setName($profileName);

            $event->before(function () use ($pingParams, $profiler) {
                $profiler->start();
                CronWatcherClient::ping($pingParams, 'running');
            });

            $event->onSuccess(function () use ($pingParams) {
                CronWatcherClient::ping($pingParams, 'success');
            });

            $event->onFailureWithOutput(function ($output) {
                // Do nothing for now, but defined
            });

            $event->onFailure(function () use ($logFile, $pingParams) {

                $failedText = file_exists($logFile) ? file_get_contents($logFile) : '';
                CronWatcherClient::ping($pingParams, 'failed', $failedText);
            });

            $event->after(function () use ($logFile, $profiler, $timeStamp, $pingParams) {
                @unlink($logFile);
                $profiler->stop();
                $pingParams['metrics'] = $profiler->getMetrics();
                CronWatcherClient::sendMetrics($pingParams);
            });


        }
    }

}
