<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandPermission;
use Bytes\DiscordResponseBundle\Objects\Slash\PartialGuildApplicationCommandPermission;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class BulkBulkEditCommandsPermissionsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
class BulkEditCommandsPermissionsTest extends TestDiscordBotClientCase
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
    public function testBulkEditCommandsPermissions($command, $guild, $roleOrUser, $type, $allow, $permission)
    {
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/bulk-edit-commands-permissions-success.json')));

        $response = $client->bulkEditCommandsPermissions($guild, [PartialGuildApplicationCommandPermission::create($command, [$permission])]);
        $this->assertResponseIsSuccessful($response);
        $this->assertResponseStatusCodeSame($response, Response::HTTP_OK);
        $this->assertResponseHasContent($response);
        $this->assertResponseContentSame($response, Fixture::getFixturesData('HttpClient/bulk-edit-commands-permissions-success.json'));
    }

    /**
     * @dataProvider provideGuildCommandPermission
     * @param $command
     * @param $guild
     * @param $roleOrUser
     * @param $type
     * @param $allow
     * @param $permission
     * @throws NoTokenException
     * @throws TransportExceptionInterface
     */
    public function testBulkEditCommandsPermissionsInvalidPermissions($command, $guild, $roleOrUser, $type, $allow, $permission)
    {
        $this->expectException(ValidatorException::class);
        $client = $this->setupClient(MockClient::requests(
            MockJsonResponse::makeFixture('HttpClient/bulk-edit-commands-permissions-success.json')));

        $client->bulkEditCommandsPermissions($guild, [ApplicationCommandPermission::create($roleOrUser, $type, $allow)]);
    }
}