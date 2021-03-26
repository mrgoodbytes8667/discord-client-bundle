<?php


namespace Bytes\DiscordBundle\Tests\MockHttpClient;


use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Illuminate\Support\Arr;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockClient
 * @package Bytes\DiscordBundle\Tests\MockHttpClient
 */
class MockClient
{
    /**
     * @return MockHttpClient
     */
    public static function empty()
    {
        return static::client(new MockEmptyResponse());
    }

    /**
     * @param mixed ...$responseFactory
     * @return MockHttpClient
     */
    public static function client(...$responseFactory)
    {
        // Flatten the array down by one layer if we're nested
        if (count($responseFactory) == 1) {
            $first = Arr::first($responseFactory);
            if (is_array($first)) {
                $responseFactory = $first;
            }
        }
        return new MockHttpClient($responseFactory);
    }

    /**
     * @return MockHttpClient
     */
    public static function emptyBadRequest()
    {
        return static::client(new MockEmptyResponse(Response::HTTP_BAD_REQUEST));
    }

    /**
     * @param int $code
     * @return MockHttpClient
     */
    public static function emptyError(int $code)
    {
        return static::client(new MockEmptyResponse($code));
    }

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
     * @param mixed ...$requests
     * @return MockHttpClient
     */
    public static function requests(...$requests)
    {
        return static::client($requests);
    }
}