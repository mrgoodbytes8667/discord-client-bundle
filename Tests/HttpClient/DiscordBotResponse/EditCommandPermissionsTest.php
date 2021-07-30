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
 * Class EditCommandsPermissionsTest
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotResponse
 */
class EditCommandPermissionsTest extends TestDiscordBotClientCase
{
    use CommandPermissionsProviderTrait;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testEditCommandPermissions()
    {
        $response = $this
            ->setupResponse('HttpClient/edit-command-permissions-success.json', type: GuildApplicationCommandPermission::class)
            ->deserialize();

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