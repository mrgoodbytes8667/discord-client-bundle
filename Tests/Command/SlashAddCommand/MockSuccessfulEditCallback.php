<?php


namespace Bytes\DiscordBundle\Tests\Command\SlashAddCommand;


use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;

/**
 * Class MockSuccessfulEditCallback
 * @package Bytes\DiscordBundle\Tests\Command\SlashAddCommand
 */
class MockSuccessfulEditCallback extends MockClientCallbackIterator
{
    /**
     * MockSuccessfulEditCallback constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/get-guilds.json'),
                MockJsonResponse::makeFixture('SlashAddCommandTest/edit-command-success.json'),
            ]
        );
    }
}