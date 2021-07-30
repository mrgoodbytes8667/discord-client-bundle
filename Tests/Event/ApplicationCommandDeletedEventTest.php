<?php

namespace Bytes\DiscordClientBundle\Tests\Event;

use Bytes\DiscordClientBundle\Event\ApplicationCommandDeletedEvent;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ApplicationCommandDeletedEventTest extends TestCase
{
    use ApplicationCommandEventTestTrait;

    /**
     * @return string
     */
    protected static function getTestClass(): string
    {
        return ApplicationCommandDeletedEvent::class;
    }
}