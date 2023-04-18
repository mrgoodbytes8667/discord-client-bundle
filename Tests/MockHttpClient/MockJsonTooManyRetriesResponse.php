<?php


namespace Bytes\DiscordClientBundle\Tests\MockHttpClient;


use DateInterval;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MockJsonTooManyRetriesResponse
 * @package Bytes\DiscordClientBundle\Tests\MockHttpClient
 */
class MockJsonTooManyRetriesResponse extends MockJsonResponse
{
    /**
     * MockJsonTooManyRetriesResponse constructor.
     * @param float|null $retryAfter Number in seconds
     * @param array $info = ResponseInterface::getInfo()
     * @throws Exception
     *
     * @see ResponseInterface::getInfo() for possible info, e.g. "response_headers"
     */
    public function __construct(?float $retryAfter = null, array $info = [])
    {
        if (($retryAfter ?? 0) <= 0) {
            $retryAfter = rand(0, 2000) / 1000;
        }

        $reset = new DateTime();
        $reset->add(new DateInterval(sprintf('PT%dS', ceil($retryAfter))));

        $body = json_encode(["message" => "You are being rate limited.", "retry_after" => $retryAfter, "global" => false]);
        $info['response_headers']['X-RateLimit-Remaining'] = 0;
        $info['response_headers']['X-RateLimit-Reset'] = $reset->getTimestamp();
        $info['response_headers']['X-RateLimit-Reset-After'] = (int)$retryAfter;
        parent::__construct($body, Response::HTTP_TOO_MANY_REQUESTS, $info);
    }
}
