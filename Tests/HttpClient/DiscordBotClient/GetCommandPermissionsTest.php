<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetCommandsPermissionsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class GetCommandPermissionsTest extends TestDiscordBotClientCase
{
    use CommandPermissionsProviderTrait;

    /**
     * @dataProvider provideGuildCommand
     * @param $command
     * @param $guild
     * @throws ClientExceptionInterface
     * @throws NoTokenException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCommandPermissions($command, $guild)
    {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/get-command-permissions-success.json')));

        $response = $client->getCommandPermissions($guild, $command);
        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/get-command-permissions-success.json'));
    }
}