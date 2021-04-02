<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CreateCommandTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class CreateCommandTest extends TestDiscordBotClientCase
{
    /**
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testCreateCommand()
    {
        $guilds = $this
            ->setupResponse('HttpClient/get-command-success.json', type: ApplicationCommand::class)
            ->deserialize();

        $this->assertInstanceOf(ApplicationCommand::class, $guilds);
    }
}


