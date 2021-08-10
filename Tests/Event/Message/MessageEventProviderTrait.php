<?php

namespace Bytes\DiscordClientBundle\Tests\Event\Message;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordResponseBundle\Objects\Channel;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\MessageReference;
use Generator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;

/**
 *
 */
trait MessageEventProviderTrait
{
    use TestDiscordFakerTrait;
    
    /**
     * @return Generator
     */
    public function provideMessageReference()
    {
        yield [MessageReference::create('123', '456', '789')];
    }

    /**
     * @return Generator
     */
    public function provideThread()
    {
        yield [new Channel()];
    }

    /**
     * @return Generator
     */
    public function provideComponents()
    {
        yield ['count' => 1, 'components' => [new Message\Component()]];
        yield ['count' => 5, 'components' => [new Message\Component(), new Message\Component(), new Message\Component(), new Message\Component(), new Message\Component()]];
    }

    /**
     * @return Generator
     */
    public function provideEntityIds()
    {
        $this->setupFaker();
        yield [$this->faker->randomDigit()];
        yield [1];
        yield [999999999999];
        yield [Uuid::v1()];
        yield [Uuid::v4()];
        yield [new Ulid()];
        yield [$this->faker->uuid()];
    }
}