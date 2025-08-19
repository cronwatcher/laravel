<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Console\Commands;

use CronWatcher\Laravel\Event;
use CronWatcher\Laravel\Settings;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UpdateCronWatcher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronwatcher:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It synchronizes cronjobs on CronWatcher server';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $schedule = app(Schedule::class);
        $commands = Artisan::all();

        $eventsToRegister = [];

        /** @var \Illuminate\Console\Scheduling\Event $event */
        foreach ($schedule->events() as $event) {
            $expression  = $event->getExpression();
            $commandName = Event::getCommand($event);
            $description = \CronWatcher\Laravel\Command::getCommandDescription($commandName, $commands);

            $pingParams = [
                'expression'  => $expression,
                'command'     => $commandName,
                'description' => $description,
            ];

            $eventsToRegister[] = $pingParams;
        }

        try {
            $syncData = [
                'events'          => $eventsToRegister,
                'client_timezone' => Settings::getTimezone(),
                'laravel_version' => app()->version(),
                'php_version'     => PHP_VERSION,
            ];

            $response = Http::withToken(Settings::getToken())->withHeaders(['Accept' => 'application/json'])
                ->post(Settings::getUrl() . '/events/sync', $syncData)
            ;

            if ($response->failed()) {
                $this->error("Cronjobs didn't synced successfully! -> " . $response->body());
                Log::channel('cronwatcher')->error("Cronjobs didn't synced successfully! -> " . $response->body());

                return CommandAlias::FAILURE;
            }

            $this->info('Cronjobs synced successfully!');

            return CommandAlias::SUCCESS;
        } catch (ConnectionException $connectionException) {
            $this->error("Cronjobs didn't synced successfully! -> " . $connectionException->getMessage());
            Log::channel('cronwatcher')->error("Cronjobs didn't synced successfully! -> " . $connectionException->getMessage());
        } catch (\Exception $exception) {
            $this->error("Cronjobs didn't synced successfully! -> " . $exception->getMessage());
            Log::channel('cronwatcher')->error("Cronjobs didn't synced successfully! -> " . $exception->getMessage());
        }

        return CommandAlias::FAILURE;
    }
}
