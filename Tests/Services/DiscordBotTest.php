<?php

namespace Bytes\DiscordBundle\Tests\Services;

use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Services\Client\DiscordBot;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordBundle\Tests\TestDiscordGuildTrait;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
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
    use TestFullValidatorTrait, TestFullSerializerTrait, CommandProviderTrait, TestDiscordGuildTrait;

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
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommandsFailure($cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getCommands($guild);
    }

    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommand($cmd, ?IdInterface $guild)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-command-success.json'),
        ]));

        $command = $client->getCommand($cmd, $guild);

        $this->assertInstanceOf(ApplicationCommand::class, $command);
        $this->assertEquals('sample', $command->getName());

        $commandId = $cmd instanceof IdInterface ? $cmd->getId() : $cmd;

        $this->assertEquals($commandId, $command->getId());
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommandFailure($cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getCommand($cmd, $guild);
    }

    /**
     * @dataProvider provideValidGetGuildFixtureFiles
     */
    public function testGetGuild(string $file, bool $withCounts)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $guild = $client->getGuild('737645596567095093', $withCounts);
        $this->validateClientGetGuildAsGuild($guild, '737645596567095093', 'Sample Server Alpha', '38ee303112b61ab351dbafdc50e094d8', '282017982734073856', 2, $withCounts);
    }

    /**
     * @dataProvider provideValidGetGuildFixtureFiles
     */
    public function testGetGuildPartial(string $file, bool $withCounts)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $guild = $client->getGuild('737645596567095093', $withCounts, [], PartialGuild::class);
        $this->validateClientGetGuildAsPartialGuild($guild, '737645596567095093', 'Sample Server Alpha', '38ee303112b61ab351dbafdc50e094d8', $withCounts);
    }

    /**
     * @return \Generator
     */
    public function provideValidGetGuildFixtureFiles()
    {
        yield ['file' => 'HttpClient/get-guild-success.json', 'withCounts' => false];
        yield ['file' => 'HttpClient/get-guild-with-counts-success.json', 'withCounts' => true];
    }
}
