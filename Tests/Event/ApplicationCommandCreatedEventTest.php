<?php

namespace Bytes\DiscordClientBundle\Tests\Event;

use Bytes\DiscordClientBundle\Event\ApplicationCommandCreatedEvent;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ApplicationCommandCreatedEventTest extends TestCase
{
    use ApplicationCommandEventTestTrait;

    /**
     * @return string
     */
    protected static function getTestClass(): string
    {
        return ApplicationCommandCreatedEvent::class;
    }
}