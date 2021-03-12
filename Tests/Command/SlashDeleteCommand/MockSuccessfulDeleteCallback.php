<?php


namespace Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand;


use Bytes\DiscordBundle\Tests\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockSuccessfulDeleteCallback
 * @package Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand
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