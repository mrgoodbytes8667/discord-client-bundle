<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordResponseBundle\Objects\Message;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateMessageTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class CreateMessageTest extends TestDiscordBotClientCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCreateMessage()
    {
        /** @var Message $message */
        $message = $this
            ->setupResponse('HttpClient/create-message-success.json', type: Message::class)
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
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCreateMessageWithFollowup()
    {
        /** @var Message $message */
        $message = $this
            ->setupResponse('HttpClient/create-message-with-followup-success.json', type: Message::class)
            ->deserialize();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam a justo id elit pharetra dapibus non eget massa. Suspendisse pretium enim ac malesuada iaculis. Donec in mattis erat, non molestie nisl. Suspendisse aliquet laoreet mauris, quis porta ipsum convallis et. Etiam porttitor fermentum velit, eu molestie tortor aliquam eu. Ut.", $message->getContent());
        $this->assertEquals('cormier.macie', $message->getAuthor()->getUsername());
        $this->assertEquals('293324682303491310', $message->getId());
        $this->assertEquals(0, $message->getType());
        $this->assertEquals('236148027649769274', $message->getChannelID());
        $this->assertFalse($message->getPinned());
        $this->assertFalse($message->getMentionEveryone());
        $this->assertFalse($message->getTts());
        $this->assertCount(1, $message->getEmbeds());
        $this->assertInstanceOf(Message::class, $message->getReferencedMessage());
    }
}