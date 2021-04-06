<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DeleteAllCommandsTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient
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