<?php

namespace Bytes\DiscordClientBundle\Tests\Event;

use Bytes\DiscordClientBundle\Event\ApplicationCommandCreatedEvent;
use Bytes\DiscordResponseBundle\Objects\Interfaces\ApplicationCommandInterface;

trait ApplicationCommandEventTestTrait
{
    abstract protected static function getTestClass(): string;

    public function testGetSetApplicationCommand()
    {
        $class = self::getTestClass();
        $mock = $this
            ->getMockBuilder(ApplicationCommandInterface::class)
            ->getMock();

        $object = new $class();
        $this->assertNull($object->getApplicationCommand());
        $this->assertInstanceOf($class, $object->setApplicationCommand(null));
        $this->assertNull($object->getApplicationCommand());
        $this->assertInstanceOf($class, $object->setApplicationCommand($mock));
        $this->assertEquals($mock, $object->getApplicationCommand());
    }
}