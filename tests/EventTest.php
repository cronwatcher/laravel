<?php
namespace CronWatcher\Laravel\Tests;

use CronWatcher\Laravel\Event;
use Illuminate\Console\Scheduling\Event as LaravelEvent;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetCommandReturnsNullIfCommandAndDescriptionAreNull()
    {
        $mock = $this->createMock(LaravelEvent::class);
        $mock->command = null;
        $mock->description = null;

        $this->assertNull(Event::getCommand($mock));
    }

    /**
     * @throws Exception
     */
    public function testGetCommandReturnsDescriptionIfCommandIsNull()
    {
        $mock = $this->createMock(LaravelEvent::class);
        $mock->command = null;
        $mock->description = 'Test description';

        $this->assertEquals('Test description', Event::getCommand($mock));
    }

    /**
     * @throws Exception
     */
    public function testGetCommandReturnsCommandIfNotArtisan()
    {
        $mock = $this->createMock(LaravelEvent::class);
        $mock->command = '/usr/bin/php some-script.php';
        $mock->description = 'desc';

        $this->assertEquals('/usr/bin/php some-script.php', Event::getCommand($mock));
    }

    /**
     * @throws Exception
     */
    public function testGetCommandReturnsTrimmedArtisanCommand()
    {
        $mock = $this->createMock(LaravelEvent::class);
        $mock->command = '/usr/bin/php artisan schedule:run';
        $mock->description = 'desc';

        $this->assertEquals('schedule:run', Event::getCommand($mock));
    }

    /**
     * @throws Exception
     */
    public function testGetCommandReturnsEmptyStringIfArtisanWithoutCommand()
    {
        $mock = $this->createMock(LaravelEvent::class);
        $mock->command = '/usr/bin/php artisan';
        $mock->description = 'desc';

        $this->assertEquals('', Event::getCommand($mock));
    }
}
