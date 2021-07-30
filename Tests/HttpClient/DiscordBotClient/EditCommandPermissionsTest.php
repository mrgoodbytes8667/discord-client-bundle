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
 * Class EditCommandsPermissionsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class EditCommandPermissionsTest extends TestDiscordBotClientCase
{
    use CommandPermissionsProviderTrait;

    /**
     * @dataProvider provideGuildCommandPermission
     * @param $command
     * @param $guild
     * @param $roleOrUser
     * @param $type
     * @param $allow
     * @param $permission
     * @throws ClientExceptionInterface
     * @throws NoTokenException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testEditCommandPermissions($command, $guild, $roleOrUser, $type, $allow, $permission)
    {

        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/edit-command-permissions-success.json')));

        $response = $client->editCommandPermissions($guild, $command, [$permission]);
        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/edit-command-permissions-success.json'));
    }
}