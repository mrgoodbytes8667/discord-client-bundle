<?php


namespace Bytes\DiscordBundle\Tests\Command\SlashAddCommand;


use Bytes\DiscordBundle\Tests\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockSuccessfulAddCallback
 * @package Bytes\DiscordBundle\Tests\Command\SlashAddCommand
 */
class MockSuccessfulAddCallback extends MockClientCallbackIterator
{
    /**
     * MockSuccessfulAddCallback constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                MockJsonResponse::makeFixture('SlashAddCommandTest/get-guilds.json'),
                MockJsonResponse::makeFixture('SlashAddCommandTest/add-command-success.json', Response::HTTP_CREATED),
            ]
        );
    }
}