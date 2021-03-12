<?php


namespace Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand;


use Bytes\DiscordBundle\Tests\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonTooManyRetriesResponse;
use Exception;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockSuccessfulDeleteWithRetriesCallback
 * @package Bytes\DiscordBundle\Tests\Command\SlashDeleteCommand
 */
class MockSuccessfulDeleteWithRetriesCallback extends MockClientCallbackIterator
{
    /**
     * MockSuccessfulDeleteWithRetriesCallback constructor.
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                new MockJsonTooManyRetriesResponse(0.123),
                new MockJsonTooManyRetriesResponse(0.123),
                new MockJsonTooManyRetriesResponse(0.123),
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/get-guilds.json'),

                new MockResponse('', ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/get-commands.json'),

                new MockResponse('', ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
                MockJsonResponse::makeFixture('SlashDeleteCommandTest/delete-command.json', Response::HTTP_NO_CONTENT),
            ]
        );
    }
}