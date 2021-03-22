<?php

namespace Bytes\DiscordBundle\Tests\Services;

use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Services\Client\DiscordBot;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordBotTest
 * @package Bytes\DiscordBundle\Tests\Services
 */
class DiscordBotTest extends TestCase
{
    use TestFullValidatorTrait, TestFullSerializerTrait, CommandProviderTrait;

    /**
     *
     */
    public function testGetCommands()
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-commands-success.json'),
        ]));

        $commands = $client->getCommands();

        $this->assertIsArray($commands);
        $this->assertCount(1, $commands);

        $this->assertInstanceOf(ApplicationCommand::class, $commands[0]);
        $this->assertEquals('sample', $commands[0]->getName());
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordBot
     */
    protected function setupClient(HttpClientInterface $httpClient)
    {
        $client = new DiscordBotClient($httpClient, new DiscordRetryStrategy(), $this->validator, $this->serializer, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT);
        return new DiscordBot($client, $this->serializer);
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param ApplicationCommand $cmd
     * @param IdInterface|null $guild
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommandsFailure(ApplicationCommand $cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getCommands($guild);
    }

    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param ApplicationCommand $cmd
     * @param IdInterface|null $guild
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommand(ApplicationCommand $cmd, ?IdInterface $guild)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-command-success.json'),
        ]));

        $command = $client->getCommand($cmd, $guild);

        $this->assertInstanceOf(ApplicationCommand::class, $command);
        $this->assertEquals('sample', $command->getName());

        $this->assertEquals($cmd->getId(), $command->getId());
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param ApplicationCommand $cmd
     * @param IdInterface|null $guild
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommandFailure(ApplicationCommand $cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getCommand($cmd, $guild);
    }
}
