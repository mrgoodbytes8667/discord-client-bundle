<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class BulkOverwriteCommandTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class BulkOverwriteCommandTest extends TestDiscordBotClientCase
{
    use CommandProviderTrait;

    /**
     * @dataProvider provideBulkOverwriteCommand
     * @param $commands
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testBulkOverwriteCommand($commands)
    {
        $commands = $this
            ->setupResponse('HttpClient/bulk-overwrite-global-success.json', type: '\Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand[]')
            ->deserialize();

        $this->assertCount(5, $commands);
    }

    /**
     * @dataProvider provideBulkOverwriteCommand
     * @param $commands
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testBulkOverwriteGuildCommand($commands)
    {
        $commands = $this
            ->setupResponse('HttpClient/bulk-overwrite-guild-success.json', type: '\Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand[]')
            ->deserialize();

        $this->assertCount(5, $commands);
    }
}