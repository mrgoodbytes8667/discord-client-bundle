<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\CommandProviderTrait;
use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetCommandTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class GetCommandTest extends TestDiscordBotClientCase
{
    use CommandProviderTrait;

    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param $cmd
     * @param IdInterface|null $guild
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommand($cmd, ?IdInterface $guild)
    {
        $command = $this
            ->setupResponse('HttpClient/get-command-success.json', type: ApplicationCommand::class)
            ->deserialize();

        $this->assertInstanceOf(ApplicationCommand::class, $command);
        $this->assertEquals('sample', $command->getName());

        $commandId = $cmd instanceof IdInterface ? $cmd->getId() : $cmd;

        $this->assertEquals($commandId, $command->getId());
    }
}

