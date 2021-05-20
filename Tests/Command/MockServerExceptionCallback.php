<?php


namespace Bytes\DiscordClientBundle\Tests\Command;


use Bytes\Tests\Common\MockHttpClient\MockClientCallbackIterator;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockServerExceptionCallback
 * @package Bytes\DiscordClientBundle\Tests\Command
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