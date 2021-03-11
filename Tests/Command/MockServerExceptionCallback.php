<?php


namespace Bytes\DiscordBundle\Tests\Command;


use Bytes\DiscordBundle\Tests\MockHttpClient\MockClientCallbackIterator;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockServerExceptionCallback
 * @package Bytes\DiscordBundle\Tests\Command
 */
class MockServerExceptionCallback extends MockClientCallbackIterator
{
    /**
     * MockServerExceptionCallback constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setArray(
            [
                new MockResponse('', ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
                new MockResponse('', ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
                new MockResponse('', ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
                new MockResponse('', ['http_code' => Response::HTTP_INTERNAL_SERVER_ERROR]),
            ]
        );
    }
}