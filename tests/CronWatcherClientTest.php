<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Tests;

use CronWatcher\Laravel\CronWatcherClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class CronWatcherClientTest extends TestCase
{
    public function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testPingSuccessLogsNothingOnSuccessResponse()
    {
        $pingParams = ['foo' => 'bar'];
        $status     = 'ok';
        $message    = 'test';

        // Instead, set config values so real Settings class works
        config(['cronwatcher.key' => 'token']);
        config(['app.timezone' => 'Europe/Berlin']);
        // If needed, you can also set app.schedule_timezone
        config(['app.schedule_timezone' => null]);

        // Mock HTTP
        $response = \Mockery::mock(Response::class);
        $response->shouldReceive('failed')->andReturn(false);
        Http::shouldReceive('withToken')->with('token')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andReturn($response);

        // Log should not be called
        Log::shouldReceive('channel')->never();

        CronWatcherClient::ping($pingParams, $status, $message);
        $this->assertTrue(true);
    }

    public function testPingLogsErrorOnFailedResponse()
    {
        $pingParams = ['foo' => 'bar'];
        $status     = 'fail';
        $message    = 'fail message';

        config(['cronwatcher.key' => 'token']);
        config(['app.timezone' => 'Europe/Berlin']);
        config(['app.schedule_timezone' => null]);

        $response = \Mockery::mock(Response::class);
        $response->shouldReceive('failed')->andReturn(true);
        $response->shouldReceive('body')->andReturn('error-body');
        Http::shouldReceive('withToken')->with('token')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andReturn($response);

        $logMock = \Mockery::mock();
        $logMock->shouldReceive('error')->with('error-body')->once();
        Log::shouldReceive('channel')->with('cronwatcher')->andReturn($logMock);

        CronWatcherClient::ping($pingParams, $status, $message);
        $this->assertTrue(true);
    }

    public function testPingLogsConnectionException()
    {
        $pingParams = ['foo' => 'bar'];
        $status     = 'fail';
        $message    = 'fail message';

        config(['cronwatcher.key' => 'token']);
        config(['app.timezone' => 'Europe/Berlin']);
        config(['app.schedule_timezone' => null]);

        Http::shouldReceive('withToken')->with('token')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andThrow(new ConnectionException('connection error'));

        $logMock = \Mockery::mock();
        $logMock->shouldReceive('error')->with('connection error')->once();
        Log::shouldReceive('channel')->with('cronwatcher')->andReturn($logMock);

        CronWatcherClient::ping($pingParams, $status, $message);
        $this->assertTrue(true);
    }

    public function testPingLogsGenericException()
    {
        $pingParams = ['foo' => 'bar'];
        $status     = 'fail';
        $message    = 'fail message';

        config(['cronwatcher.key' => 'token']);
        config(['app.timezone' => 'Europe/Berlin']);
        config(['app.schedule_timezone' => null]);

        Http::shouldReceive('withToken')->with('token')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andThrow(new \Exception('generic error'));

        $logMock = \Mockery::mock();
        $logMock->shouldReceive('error')->with('generic error')->once();
        Log::shouldReceive('channel')->with('cronwatcher')->andReturn($logMock);

        CronWatcherClient::ping($pingParams, $status, $message);
        $this->assertTrue(true);
    }
}
