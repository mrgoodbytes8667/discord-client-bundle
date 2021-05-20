<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Generator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateMessageTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class CreateMessageTest extends TestDiscordBotClientCase
{
    use TestCreateEditMessageTrait;

    /**
     * @dataProvider provideFixtureFileWithoutReference
     * @param $file
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCreateMessage($file)
    {
        $message = $this->getMessage($file);
        $this->assertNull($message->getEditedTimestamp());
    }

    /**
     * @dataProvider provideFixtureFileWithReference
     * @param $file
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testCreateMessageWithResponse($file)
    {
        $message = $this->getMessage($file);
        $this->assertNull($message->getEditedTimestamp());
    }

    /**
     * @return Generator
     */
    public function provideFixtureFileWithoutReference(): Generator
    {
        yield ['HttpClient/create-message-success.json'];
    }

    /**
     * @return Generator
     */
    public function provideFixtureFileWithReference(): Generator
    {
        yield ['HttpClient/create-message-with-followup-success.json'];
    }
}