<?php


namespace Bytes\DiscordBundle\Tests\Command;


use Bytes\DiscordBundle\Tests\MockHttpClient\MockClientCallbackIterator;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonResponse;
use Bytes\DiscordBundle\Tests\MockHttpClient\MockJsonTooManyRetriesResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockTooManyRequestsCallback
 * @package Bytes\DiscordBundle\Tests\Command
 */
class MockTooManyRequestsCallback extends MockClientCallbackIterator
{
    /**
     * MockTooManyRequestsCallback constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                new MockJsonTooManyRetriesResponse(0.123),
                new MockJsonTooManyRetriesResponse(0.123),
                new MockJsonTooManyRetriesResponse(0.123),
                new MockJsonTooManyRetriesResponse(0.123),
            ]
        );
    }
}