<?php


namespace Bytes\DiscordClientBundle\Tests\Command\SlashDeleteCommand;


use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;

/**
 *
 */
class MockSuccessfulGetGuildsCallback extends MockClientCallbackIterator
{
    /**
     * MockSuccessfulDeleteCallback constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/get-guilds.json'),
            ]
        );
    }
}