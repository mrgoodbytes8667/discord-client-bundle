<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordResponseBundle\Objects\Message;

/**
 * Class EditMessageTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 * @deprecated Not deprecated but this way I can see this easily in the IDE!
 */
class EditMessageTest extends TestDiscordBotClientCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testEditMessage()
    {
        $guilds = $this
            ->setupResponse('HttpClient/get.json', type: Message::class)
            ->deserialize();

        //$this->assertCount(2, $guilds);
    }
}

