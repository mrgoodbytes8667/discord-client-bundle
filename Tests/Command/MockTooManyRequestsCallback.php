<?php


namespace Bytes\DiscordClientBundle\Tests\Command;


use Bytes\DiscordClientBundle\Tests\MockHttpClient\MockJsonTooManyRetriesResponse;
use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Exception;

/**
 * Class MockTooManyRequestsCallback
 * @package Bytes\DiscordClientBundle\Tests\Command
 */
class MockTooManyRequestsCallback extends MockClientCallbackIterator
{
    /**
     * MockTooManyRequestsCallback constructor.
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            self::getResponses()
        );
    }

    /**
     * @return MockJsonTooManyRetriesResponse[]
     * @throws Exception
     */
    public static function getResponses(float $retryAfter = 0.123)
    {
        return [
            new MockJsonTooManyRetriesResponse($retryAfter),
            new MockJsonTooManyRetriesResponse($retryAfter),
            new MockJsonTooManyRetriesResponse($retryAfter),
            new MockJsonTooManyRetriesResponse($retryAfter),
        ];
    }
}