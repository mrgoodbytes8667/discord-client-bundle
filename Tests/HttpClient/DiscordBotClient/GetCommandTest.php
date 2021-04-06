<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use InvalidArgumentException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetCommandTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class GetCommandTest extends TestDiscordBotClientCase
{
    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     */
    public function testGetCommand($cmd, ?IdInterface $guild)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-command-success.json'),
        ]));

        $response = $client->getCommand($cmd, $guild);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-command-success.json'));
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     */
    public function testGetCommandFailure($cmd, ?IdInterface $guild, int $code)
    {
        $client = $this->setupClient(MockClient::emptyError($code));
        $response = $client->getCommand($cmd, $guild);

        $this->assertResponseStatusCodeSame($response, $code);
    }

    /**
     * @dataProvider provideInvalidCommandAndValidGuild
     * @param $cmd
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetCommandBadCommandArgument($cmd, $guild)
    {
        $this->expectExceptionMessage('The "applicationCommand" argument is required and cannot be blank.');
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getCommand($cmd, $guild);
    }

    /**
     * @dataProvider provideValidCommandAndInvalidNotEmptyGuild
     * @param $command
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testCreateCommandBadGuildArgument($command, $guild)
    {
        $this->expectExceptionMessage('The "guildId" argument must be a string, must implement GuildIdInterface/IdInterface, or be null.');
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getCommand($command, $guild);
    }
}

