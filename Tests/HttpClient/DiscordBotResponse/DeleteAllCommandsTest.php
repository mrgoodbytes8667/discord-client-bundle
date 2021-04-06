<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteAllCommandsTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class DeleteAllCommandsTest extends TestDiscordBotClientCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testDeleteAllCommands()
    {
        $commands = $this
            ->setupResponse(content: '[]', type: '\Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand[]')
            ->deserialize();

        $this->assertCount(0, $commands);
    }
}