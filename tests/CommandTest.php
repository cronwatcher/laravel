<?php

declare(strict_types=1);

namespace CronWatcher\Laravel\Tests;

use CronWatcher\Laravel\Command;
use Orchestra\Testbench\TestCase;
use Illuminate\Console\Command as IlluminateCommand;

class CommandTest extends TestCase
{
    public function testReturnsDescriptionIfCommandExistsAndIsIlluminateCommand()
    {
        $mockCommand = $this->getMockBuilder(IlluminateCommand::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCommand->method('getDescription')->willReturn('Test description');

        $commands = [
            'test:command' => $mockCommand,
        ];

        $result = Command::getCommandDescription('test:command', $commands);
        $this->assertSame('Test description', $result);
    }

    public function testReturnsEmptyStringIfCommandDoesNotExist()
    {
        $commands = [];
        $result = Command::getCommandDescription('nonexistent:command', $commands);
        $this->assertSame('', $result);
    }

    public function testReturnsEmptyStringIfCommandIsNotIlluminateCommand()
    {
        $commands = [
            'test:command' => new \stdClass(),
        ];
        $result = Command::getCommandDescription('test:command', $commands);
        $this->assertSame('', $result);
    }

    public function testReturnsEmptyStringIfCommandNameIsNull()
    {
        $mockCommand = $this->getMockBuilder(IlluminateCommand::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCommand->method('getDescription')->willReturn('Should not be returned');

        $commands = [
            'test:command' => $mockCommand,
        ];
        $result = Command::getCommandDescription(null, $commands);
        $this->assertSame('', $result);
    }
}

