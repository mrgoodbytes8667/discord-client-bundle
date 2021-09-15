<?php


namespace Bytes\DiscordClientBundle\Tests\Command\SlashPermissionsCommand;


use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;

/**
 *
 */
class MockSuccessfulPermissionsCallback extends MockClientCallbackIterator
{
    /**
     * MockSuccessfulPermissionsCallback constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                MockJsonResponse::makeFixture('SlashPermissionsCommandTest/get-guilds.json'),
                MockJsonResponse::makeFixture('SlashPermissionsCommandTest/get-commands.json'),
                MockJsonResponse::makeFixture('SlashPermissionsCommandTest/get-roles.json'),
                MockJsonResponse::makeFixture('SlashPermissionsCommandTest/get-existing-permissions.json'),
                MockJsonResponse::makeFixture('SlashPermissionsCommandTest/get-existing-permissions.json'),
            ]
        );
    }
}