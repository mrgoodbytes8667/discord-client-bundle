<?php

namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\DiscordBundle\HttpClient\DiscordClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordClientTest
 * @package Bytes\DiscordBundle\Tests\HttpClient
 */
class DiscordClientTest extends TestHttpClientCase
{
    use TestDiscordClientTrait;

    protected function setupClient(HttpClientInterface $httpClient)
    {
        return new DiscordClient($httpClient, new DiscordRetryStrategy(), $this->validator, $this->serializer, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT);
    }
}
