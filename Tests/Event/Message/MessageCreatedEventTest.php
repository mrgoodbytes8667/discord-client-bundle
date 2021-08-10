<?php

namespace Bytes\DiscordClientBundle\Tests\Event\Message;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Event\Message\MessageCreatedEvent;
use Bytes\Tests\Common\DataProvider\NullProviderTrait;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class MessageCreatedEventTest extends TestCase
{
    use TestDiscordFakerTrait, NullProviderTrait, MessageEventProviderTrait;

    /**
     * @dataProvider provideMessageReference
     */
    public function testGetSetMessageReference($ref)
    {
        $message = new MessageCreatedEvent();
        $this->assertNull($message->getMessageReference());
        $this->assertInstanceOf(MessageCreatedEvent::class, $message->setMessageReference(null));
        $this->assertNull($message->getMessageReference());
        $this->assertInstanceOf(MessageCreatedEvent::class, $message->setMessageReference($ref));
        $this->assertEquals($ref, $message->getMessageReference());
    }

    /**
     * @dataProvider provideThread
     * @dataProvider provideNull
     * @param $thread
     */
    public function testGetSetThread($thread)
    {
        $message = new MessageCreatedEvent();
        $this->assertNull($message->getThread());
        $this->assertInstanceOf(MessageCreatedEvent::class, $message->setThread(null));
        $this->assertNull($message->getThread());
        $this->assertInstanceOf(MessageCreatedEvent::class, $message->setThread($thread));
        $this->assertEquals($thread, $message->getThread());
    }

    /**
     * @dataProvider provideComponents
     * @param $count
     * @param $components
     */
    public function testGetSetComponents($count, $components)
    {
        $message = new MessageCreatedEvent();
        $this->assertNull($message->getComponents());
        $this->assertInstanceOf(MessageCreatedEvent::class, $message->setComponents(null));
        $this->assertNull($message->getComponents());
        $this->assertInstanceOf(MessageCreatedEvent::class, $message->setComponents($components));
        $this->assertCount($count, $message->getComponents());
        $this->assertEquals($components, $message->getComponents());
    }

    /**
     * @dataProvider provideEntityIds
     * @dataProvider provideNull
     * @param $entityId
     */
    public function testGetSetEntityId($entityId)
    {
        $message = new MessageCreatedEvent();
        $this->assertNull($message->getEntityId());
        $this->assertInstanceOf(MessageCreatedEvent::class, $message->setEntityId(null));
        $this->assertNull($message->getEntityId());
        $this->assertInstanceOf(MessageCreatedEvent::class, $message->setEntityId($entityId));
        $this->assertEquals($entityId, $message->getEntityId());
    }
}