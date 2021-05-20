<?php


namespace Bytes\DiscordClientBundle\Tests\MockHttpClient;


use DateInterval;
use DateTime;
use Exception;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockEmptyResponse
 * @package Bytes\DiscordClientBundle\Tests\MockHttpClient
 *
 * @deprecated v0.0.2 Replace with mrgoodbytes8667/test-common Bytes\Tests\Common\MockHttpClient
 */
class MockEmptyResponse extends MockDiscordResponse
{
    /**
     * MockEmptyResponse constructor.
     * @param int $code
     * @param array $info = ResponseInterface::getInfo()
     *
     * @see ResponseInterface::getInfo() for possible info, e.g. "response_headers"
     */
    public function __construct(int $code = Response::HTTP_NO_CONTENT, array $info = [])
    {
        parent::__construct('', $code, $info);
    }
}
