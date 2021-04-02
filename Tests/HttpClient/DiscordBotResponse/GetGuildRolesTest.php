<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Bytes\DiscordResponseBundle\Objects\Role;
use Spatie\Enum\Phpunit\EnumAssertions;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class GetGuildRolesTest
 * @package Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse
 */
class GetGuildRolesTest extends TestDiscordBotClientCase
{
    use EnumAssertions;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetGuildRoles()
    {
        /** @var Role[] $roles */
        $roles = $this
            ->setupResponse('HttpClient/get-guild-roles-success.json', type: '\Bytes\DiscordResponseBundle\Objects\Role[]')
            ->deserialize();

        $this->assertCount(8, $roles);

        $role = $roles[0];
        $this->assertEquals('745194851759249951', $role->getId());
        $this->assertEquals('@everyone', $role->getName());
        $this->assertIsString($role->getPermissions());
        $this->assertEquals('650473406', $role->getPermissions());
        $this->assertEquals(0, $role->getPosition());
        $this->assertEquals(0, $role->getColor());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetGuildRolesDeserializeErrorIntoArray()
    {

        $client = $this->setupClient(MockClient::requests(MockJsonResponse::makeJsonErrorCode(10004, 'Unknown Guild', Response::HTTP_NOT_FOUND)));
        $roles = $client->getGuildRoles('123')
            ->deserialize(false);
        
        $this->assertCount(1, $roles);
        $role = array_shift($roles);

        $this->assertSameEnumValue(JsonErrorCodes::UNKNOWN_GUILD(), $role->getCode());
        $this->assertEquals(10004, $role->getCode());
        $this->assertEquals("Unknown Guild", $role->getMessage());
    }
}