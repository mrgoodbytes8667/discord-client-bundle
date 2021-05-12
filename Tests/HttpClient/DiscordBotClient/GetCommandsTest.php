<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;


/**
 * Class GetCommandsTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class GetCommandsTest extends TestDiscordBotClientCase
{
    use GuildProviderTrait;

    /**
     * @dataProvider provideValidGuilds
     * @dataProvider provideEmptyGuilds
     * @throws TransportExceptionInterface
     */
    public function testGetCommands($guild)
    {
        $client = $this->setupClient(new MockHttpClient([
            MockJsonResponse::makeFixture('HttpClient/get-commands-success.json'),
        ]));

        $response = $client->getCommands($guild);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-commands-success.json'));
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     */
    public function testGetCommandsFailure($cmd, ?IdInterface $guild, int $code)
    {
        $client = $this->setupClient(MockClient::emptyError($code));
        $response = $client->getCommands($guild);

        $this->assertResponseStatusCodeSame($response, $code);
    }

    /**
     * @dataProvider provideInvalidNotEmptyGetGuildArguments
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testGetCommandsBadGuildArgument($guild)
    {
        $this->expectExceptionMessage('The "guildId" argument must be a string, must implement GuildIdInterface/IdInterface, or be null.');
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->getCommands($guild);
    }
}

