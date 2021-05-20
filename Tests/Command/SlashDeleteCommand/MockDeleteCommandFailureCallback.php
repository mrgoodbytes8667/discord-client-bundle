<?php


namespace Bytes\DiscordClientBundle\Tests\Command\SlashDeleteCommand;


use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockDeleteCommandFailureCallback
 * @package Bytes\DiscordClientBundle\Tests\Command\SlashDeleteCommand
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
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/get-guilds.json'),
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/get-commands.json'),
                MockJsonResponse::makeFixture('unauthorized-v6.json', Response::HTTP_UNAUTHORIZED),
            ]
        );
    }
}