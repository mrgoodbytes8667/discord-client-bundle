<?php


namespace Bytes\DiscordClientBundle\Tests\Command\SlashDeleteCommand;


use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockSuccessfulDeleteCallback
 * @package Bytes\DiscordClientBundle\Tests\Command\SlashDeleteCommand
 */
class MockSuccessfulDeleteCallback extends MockClientCallbackIterator
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
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/get-commands.json'),
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/delete-command.json', Response::HTTP_NO_CONTENT),
            ]
        );
    }
}