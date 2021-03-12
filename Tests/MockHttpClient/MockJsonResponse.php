<?php


namespace Bytes\DiscordBundle\Tests\MockHttpClient;


use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockJsonResponse
 * @package Bytes\DiscordBundle\Tests\MockHttpClient
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
        $info['response_headers']['X-RateLimit-Bucket'] = 'abcd1234';
        $info['response_headers']['X-RateLimit-Limit'] = 5;
        $info['response_headers']['X-RateLimit-Remaining'] = 4;
        $info['response_headers']['X-RateLimit-Reset-After'] = 20.000;
        $info['http_code'] = $code;
        parent::__construct($body, $info);
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
     * @param string $data
     * @param int $code
     * @param array $info
     * @return static
     */
    public static function make(string $data, int $code = Response::HTTP_OK, array $info = []) {
        return new static(json_encode($data), $code, $info);
    }
}