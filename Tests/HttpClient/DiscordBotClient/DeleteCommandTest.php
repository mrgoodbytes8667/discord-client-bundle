<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteCommandTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
 */
class DeleteCommandTest extends TestDiscordBotClientCase
{
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
     *
     */
    public function testDeleteCommandFailureBadCommandArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->setupClient(MockClient::emptyBadRequest());

        $client->deleteCommand(null, null);
    }
}

