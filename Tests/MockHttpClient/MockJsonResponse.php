<?php


namespace Bytes\DiscordClientBundle\Tests\MockHttpClient;


use Bytes\DiscordClientBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordResponseBundle\Enums\JsonErrorCodes;
use Bytes\Tests\Common\MockHttpClient\MockResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockJsonResponse
 * @package Bytes\DiscordClientBundle\Tests\MockHttpClient
 */
class MockJsonResponse extends MockResponse
{
    /**
     * MockJsonResponse constructor.
     * @param string|string[]|iterable $body The response body as a string or an iterable of strings,
     *                                       yielding an empty string simulates an idle timeout,
     *                                       exceptions are turned to TransportException
     * @param int $code
     * @param array $info = ResponseInterface::getInfo()
     *
     * @see ResponseInterface::getInfo() for possible info, e.g. "response_headers"
     */
    public function __construct($body = '', int $code = Response::HTTP_OK, array $info = [])
    {
        $info['response_headers']['Content-Type'] = 'application/json';
        parent::__construct($body, $code, $info, new MockDiscordResponseHeader());
    }

    /**
     * @param string $file
     * @param int $code
     * @param array $info
     * @return static
     */
    public static function makeFixture(string $file, int $code = Response::HTTP_OK, array $info = []) {
        return new static(Fixture::getFixturesData($file), $code, $info);
    }

    /**
     * @param string|array $data
     * @param int $code
     * @param array $info
     * @return static
     */
    public static function make($data, int $code = Response::HTTP_OK, array $info = []) {
        return new static(json_encode($data), $code, $info);
    }

    /**
     * @param JsonErrorCodes|int $jsonCode
     * @param string $message
     * @param int $code
     * @return static
     */
    public static function makeJsonErrorCode($jsonCode, string $message, int $code = Response::HTTP_BAD_REQUEST) {
        if($jsonCode instanceof JsonErrorCodes)
        {
            $jsonCode = $jsonCode->value;
        }
        $body = Fixture::getJsonErrorCodeData($jsonCode, $message, false);
        return MockJsonResponse::make($body, $code);
    }
}