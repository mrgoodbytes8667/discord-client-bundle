<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse;

use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\CommandPermissionsProviderTrait;
use Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient\TestDiscordBotClientCase;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandPermission;
use Bytes\DiscordResponseBundle\Objects\Slash\GuildApplicationCommandPermission;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class BulkBulkEditCommandsPermissionsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class BulkEditCommandsPermissionsTest extends TestDiscordBotClientCase
{
    use CommandPermissionsProviderTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testBulkEditCommandsPermissions()
    {
        /** @var GuildApplicationCommandPermission[] $response */
        $response = $this
            ->setupResponse('HttpClient/bulk-edit-commands-permissions-success.json', type: '\Bytes\DiscordResponseBundle\Objects\Slash\GuildApplicationCommandPermission[]')
            ->deserialize();

        $this->assertCount(1, $response);

        $response = array_shift($response);

        $this->assertInstanceOf(GuildApplicationCommandPermission::class, $response);

        $this->assertEquals('471965345773546119', $response->getId());
        $this->assertEquals('498169483564721259', $response->getApplicationId());
        $this->assertEquals('645019418281109104', $response->getGuildId());

        $permissions = $response->getPermissions();
        $this->assertCount(1, $permissions);

        /** @var ApplicationCommandPermission $permission */
        $permission = array_shift($permissions);
        $this->assertInstanceOf(ApplicationCommandPermission::class, $permission);

        $this->assertEquals('312748549355900535', $permission->getId());
        $this->assertEquals(1, $permission->getType());
        $this->assertEquals(true, $permission->getPermission());
    }
}