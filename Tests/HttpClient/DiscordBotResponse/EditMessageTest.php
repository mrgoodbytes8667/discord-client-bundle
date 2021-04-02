<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Generator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class EditMessageTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class EditMessageTest extends TestDiscordBotClientCase
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
    public function testEditMessage($file)
    {
        $message = $this->getMessage($file);
        $this->assertNotNull($message->getEditedTimestamp());
    }

    /**
     * @dataProvider provideFixtureFileWithReference
     * @param $file
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testEditMessageWithResponse($file)
    {
        $message = $this->getMessage($file);
        $this->assertNotNull($message->getEditedTimestamp());
    }

    /**
     * @return Generator
     */
    public function provideFixtureFileWithoutReference(): Generator
    {
        yield ['HttpClient/edit-message-success.json'];
    }

    /**
     * @return Generator
     */
    public function provideFixtureFileWithReference(): Generator
    {
        yield ['HttpClient/edit-message-with-followup-success.json'];
    }
}