<?php

namespace CronWatcher\Laravel\Tests;

use CronWatcher\Laravel\Settings;
use Orchestra\Testbench\TestCase;

class SettingsTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cronwatcher.key', 'test-key');
        $app['config']->set('app.timezone', 'Europe/Berlin');
        $app['config']->set('app.schedule_timezone', null);
    }

    public function testReturnsCorrectUrl()
    {
        $this->assertSame('http://host.docker.internal:82/api', Settings::getUrl());
    }

    public function testReturnsTokenFromConfig()
    {
        $this->assertSame('test-key', Settings::getToken());
    }

    public function testReturnsDefaultTimezoneFromConfig()
    {
        $this->assertSame('Europe/Berlin', Settings::getTimezone());
    }

    public function testReturnsScheduleTimezoneIfSet()
    {
        config(['app.schedule_timezone' => 'UTC']);

        $this->assertSame('UTC', Settings::getTimezone());
    }

    public function testHasCorrectConstants()
    {
        $this->assertSame(600, Settings::PROFILING_MAX_EXECUTE_SECONDS);
        $this->assertSame(5, Settings::PROFILING_INTERVAL_SECONDS);
    }
}
