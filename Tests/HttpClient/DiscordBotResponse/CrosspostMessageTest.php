<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordResponseBundle\Objects\Message;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CrosspostMessageTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class CrosspostMessageTest extends TestDiscordBotClientCase
{

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testMessage()
    {
        $message = $this
            ->setupResponse('HttpClient/crosspost-message-success.json', type: Message::class)
            ->deserialize();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEmpty($message->getContent());
        $this->assertEquals('rhea17', $message->getAuthor()->getUsername());
        $this->assertEquals('234627215184254300', $message->getId());
        $this->assertEquals(0, $message->getType());
        $this->assertEquals('239800192314657438', $message->getChannelID());
        $this->assertFalse($message->getPinned());
        $this->assertFalse($message->getMentionEveryone());
        $this->assertFalse($message->getTts());
        $this->assertCount(1, $message->getEmbeds());
        $this->assertGreaterThanOrEqual(1, $message->getFlags());
    }
}