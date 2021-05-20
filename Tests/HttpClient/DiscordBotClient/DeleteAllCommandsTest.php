<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteAllCommandsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class DeleteAllCommandsTest extends TestDiscordBotClientCase
{
    /**
     * @throws TransportExceptionInterface
     */
    public function testDeleteAllCommands()
    {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::make('[]')));

        $cmd = $client->deleteAllCommands();
        $this->assertResponseIsSuccessful($cmd);
        $this->assertResponseStatusCodeSame($cmd, Response::HTTP_OK);
    }
}