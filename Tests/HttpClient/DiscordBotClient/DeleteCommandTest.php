<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\CommandProviderTrait;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteCommandTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class DeleteCommandTest extends TestDiscordBotClientCase
{
    use GuildProviderTrait, CommandProviderTrait;

    /**
     * @dataProvider provideCommandAndGuild
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommand($cmd, ?IdInterface $guild)
    {
        $client = $this->setupClient(MockClient::empty());

        $response = $client->deleteCommand($cmd, $guild);

        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_NO_CONTENT);
    }

    /**
     * @dataProvider provideCommandAndGuildClientExceptionResponses
     *
     * @param mixed $cmd
     * @param IdInterface|null $guild
     * @param int $code
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommandFailure($cmd, ?IdInterface $guild, int $code)
    {
        $this->expectException(ClientExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('HTTP %d returned for', $code));

        $client = $this->setupClient(MockClient::emptyError($code));

        $client->deleteCommand($cmd, $guild);
    }

    /**
     * @dataProvider provideInvalidCommandAndValidGuild
     * @param $command
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommandBadCommandArgument($command, $guild)
    {
        $this->expectExceptionMessage('The "applicationCommand" argument is required and cannot be blank.');
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->deleteCommand($command, $guild);
    }

    /**
     * @dataProvider provideValidCommandAndInvalidNotEmptyGuild
     * @param $command
     * @param $guild
     * @throws TransportExceptionInterface
     */
    public function testDeleteCommandBadGuildArgument($command, $guild)
    {
        $this->expectExceptionMessage('The "guildId" argument must be a string, must implement GuildIdInterface/IdInterface, or be null.');
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->deleteCommand($command, $guild);
    }
}

