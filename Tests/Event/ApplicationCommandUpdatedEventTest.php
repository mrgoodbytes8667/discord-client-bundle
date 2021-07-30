<?php

namespace Bytes\DiscordClientBundle\Tests\Event;

use Bytes\DiscordClientBundle\Event\ApplicationCommandUpdatedEvent;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ApplicationCommandUpdatedEventTest extends TestCase
{
    use ApplicationCommandEventTestTrait;

    /**
     * @return string
     */
    protected static function getTestClass(): string
    {
        return ApplicationCommandUpdatedEvent::class;
    }
}