<?php


namespace Bytes\DiscordBundle\Tests\Command\SlashAddCommand;


use Bytes\DiscordBundle\Tests\MockHttpClient\MockClientCallbackIterator;
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
                MockJsonResponse::make('SlashDeleteCommandTest/get-guilds.json'),
                MockJsonResponse::make('SlashAddCommandTest/edit-command-success.json'),
            ]
        );
    }
}