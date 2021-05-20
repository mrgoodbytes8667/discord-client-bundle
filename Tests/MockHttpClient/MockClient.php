<?php


namespace Bytes\DiscordClientBundle\Tests\MockHttpClient;


use Bytes\DiscordClientBundle\Tests\Command\MockTooManyRequestsCallback;
use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Illuminate\Support\Arr;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Bytes\Tests\Common\MockHttpClient\MockClient as BaseMockClient;

/**
 * Class MockClient
 * @package Bytes\DiscordClientBundle\Tests\MockHttpClient
 */
class MockClient extends BaseMockClient
{
    /**
     * @param JsonErrorCodes|int $jsonCode
     * @param string $message
     * @param int $code
     * @return MockHttpClient
     */
    public static function jsonErrorCode($jsonCode, string $message, int $code = Response::HTTP_BAD_REQUEST)
    {
        return static::client(MockJsonResponse::makeJsonErrorCode($jsonCode, $message, $code));
    }

    /**
     * @return MockHttpClient
     * @throws \Exception
     */
    public static function rateLimit(float $retryAfter = 0.123)
    {
        return static::client(MockTooManyRequestsCallback::getResponses($retryAfter));
    }
}