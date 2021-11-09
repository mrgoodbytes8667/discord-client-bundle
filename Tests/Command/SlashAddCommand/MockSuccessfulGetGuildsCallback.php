<?php


namespace Bytes\DiscordClientBundle\Tests\Command\SlashAddCommand;


use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;

/**
 *
 */
class MockSuccessfulGetGuildsCallback extends MockClientCallbackIterator
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                MockJsonResponse::makeFixture('SlashAddCommandTest/get-guilds.json'),
            ]
        );
    }
}