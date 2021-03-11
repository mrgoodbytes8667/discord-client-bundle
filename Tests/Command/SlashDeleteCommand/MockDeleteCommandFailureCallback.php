<?php


namespace Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand;


use Bytes\DiscordBundle\Tests\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockDeleteCommandFailureCallback
 * @package Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand
 */
class MockDeleteCommandFailureCallback extends MockClientCallbackIterator
{
    /**
     * MockDeleteCommandFailureCallback constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                MockJsonResponse::make('SlashDeleteCommandTest/get-guilds.json'),
                MockJsonResponse::make('SlashDeleteCommandTest/get-commands.json'),
                MockJsonResponse::make('unauthorized-v6.json', Response::HTTP_UNAUTHORIZED),
            ]
        );
    }
}