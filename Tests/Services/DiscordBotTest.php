<?php

namespace Bytes\DiscordBundle\Tests\Services;

use Bytes\DiscordBundle\Services\Client\DiscordBot;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\DiscordClientSetupTrait;
use Bytes\DiscordBundle\Tests\JsonErrorCodesProviderTrait;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordBundle\Tests\TestDiscordGuildTrait;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Overwrite;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use Generator;
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
    use TestFullValidatorTrait, TestFullSerializerTrait, CommandProviderTrait, TestDiscordGuildTrait, TestDiscordTrait, JsonErrorCodesProviderTrait, DiscordClientSetupTrait;

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
        $client = $this->setupBotClient($httpClient);
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
     * @return Generator
     */
    public function provideValidGetGuildFixtureFiles()
    {
        yield ['file' => 'HttpClient/get-guild-success.json', 'withCounts' => false];
        yield ['file' => 'HttpClient/get-guild-with-counts-success.json', 'withCounts' => true];
    }

    /**
     * @dataProvider provideInvalidGetGuildFixtureFiles
     */
    public function testGetGuildFailure(string $file, bool $withCounts, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getGuild($file, $withCounts);
    }

    /**
     * @return Generator
     */
    public function provideInvalidGetGuildFixtureFiles()
    {
        foreach ($this->provideClientExceptionResponses() as $clientExceptionResponse) {
            foreach ($this->provideValidGetGuildFixtureFiles() as $index => $value) {
                yield ['file' => $value['file'], 'withCounts' => $value['withCounts'], 'code' => $clientExceptionResponse['code']];
            }
        }
    }

    /**
     * @dataProvider provideValidUsers
     * @param string $file
     * @param $userId
     */
    public function testGetUser(string $file, $userId)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $user = $client->getUser($userId);
        $this->validateUser($user, '272930239796055326', 'elvie70', 'cba426068ee1c51edab2f0c38549f4bc', '6793', 0, true);
    }

    /**
     * @return Generator
     */
    public function provideValidUsers()
    {
        $user = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $user->method('getId')
            ->willReturn('230858112993375816');

        yield ['file' => 'HttpClient/get-user.json', 'userId' => '230858112993375816'];
        yield ['file' => 'HttpClient/get-user.json', 'userId' => $user];

        yield ['file' => 'HttpClient/get-me.json', 'userId' => '@me'];
    }

    /**
     * @return Generator
     */
    public function provideInvalidUsers()
    {
        foreach ($this->provideClientExceptionResponses() as $clientExceptionResponse) {
            foreach ($this->provideValidUsers() as $index => $value) {
                yield ['file' => $value['file'], 'userId' => $value['userId'], 'code' => $clientExceptionResponse['code']];
            }
        }
    }

    /**
     * @dataProvider provideInvalidUsers
     * @param string $file
     * @param $userId
     * @param int $code
     */
    public function testGetUserFailure(string $file, $userId, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(new MockHttpClient(MockJsonResponse::make('', $code)));
        $client->getUser($userId);
    }

    /**
     * @dataProvider provideValidGuild
     * @param string $file
     * @param $userId
     */
    public function testGetChannels(string $file, $guildId)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture($file),
        ]));

        $channels = $client->getChannels($guildId);
        $this->assertCount(12, $channels);

        $channel = $channels[0];
        $this->assertEquals('276921226399262614', $channel->getId());
        $this->assertEquals(6, $channel->getType());
        $this->assertEquals('721716783525430558', $channel->getGuildId());
        $this->assertEquals(5, $channel->getPosition());
        $this->assertEquals('Ad sed blanditiis incidunt quae. Et unde optio corporis. Nihil eum ad odio ab.', $channel->getName());

        $this->assertCount(3, $channel->getPermissionOverwrites());
        $overwrite = $channel->getPermissionOverwrites()[0];

        $this->assertInstanceOf(Overwrite::class, $overwrite);
        $this->assertEquals("263397620755496871", $overwrite->getId());
        $this->assertEquals('role', $overwrite->getType());
        $this->assertEquals('6546771529', $overwrite->getAllow());
        $this->assertEquals('6546771529', $overwrite->getDeny());
    }

    /**
     * @return Generator
     */
    public function provideValidGuild()
    {
        $user = $this
            ->getMockBuilder(IdInterface::class)
            ->getMock();
        $user->method('getId')
            ->willReturn('230858112993375816');

        yield ['file' => 'HttpClient/get-channels-v8-success.json', 'guildId' => '230858112993375816'];
        yield ['file' => 'HttpClient/get-channels-v8-success.json', 'guildId' => $user];
    }

    /**
     * @dataProvider provideJsonErrorCodes
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetChannelsJsonErrorCode($jsonCode, string $message, int $httpCode)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $httpCode));

        $client = $this->setupClient(MockClient::jsonErrorCode($jsonCode, $message, $httpCode));

        $channels = $client->getChannels('123');


        $this->assertCount(12, $channels);
    }
}
