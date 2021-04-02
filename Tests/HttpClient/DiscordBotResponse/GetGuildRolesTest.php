<?php

namespace Bytes\DiscordBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordResponseBundle\Objects\Role;
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
}