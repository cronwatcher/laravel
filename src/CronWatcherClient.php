<?php

declare(strict_types=1);

namespace CronWatcher\Laravel;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CronWatcherClient
{
    public static function ping(array $pingParams, string $status, ?string $message = null): void
    {
        try {
            $pingParams['status']  = $status;
            $pingParams['message'] = $message;

            $response = Http::withToken(Settings::getToken())->withHeaders(['Accept' => 'application/json'])
                ->post(Settings::getUrl() . '/events/ping', $pingParams)
            ;

            if ($response->failed()) {
                Log::channel('cronwatcher')->error($response->body());
            }
        } catch (ConnectionException $connectionException) {
            Log::channel('cronwatcher')->error($connectionException->getMessage());
        } catch (\Exception $exception) {
            Log::channel('cronwatcher')->error($exception->getMessage());
        }
    }

    public static function sendMetrics(array $metricParams): void
    {
        try {
            $response = Http::withToken(Settings::getToken())->withHeaders(['Accept' => 'application/json'])
                ->post(Settings::getUrl() . '/events/metric', $metricParams)
            ;

            if ($response->failed()) {
                Log::channel('cronwatcher')->error($response->body());
            }
        } catch (ConnectionException $connectionException) {
            Log::channel('cronwatcher')->error($connectionException->getMessage());
        } catch (\Exception $exception) {
            Log::channel('cronwatcher')->error($exception->getMessage());
        }
    }
}
