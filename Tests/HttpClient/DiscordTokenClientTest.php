<?php

namespace Bytes\DiscordBundle\Tests\HttpClient;

use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\DiscordTokenClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\DiscordBundle\Tests\TestUrlGeneratorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordTokenClientTest
 * @package Bytes\DiscordBundle\Tests\HttpClient
 *
 * @requires PHPUnit >= 9
 */
class DiscordTokenClientTest extends TestHttpClientCase
{
    use TestDiscordClientTrait, TestUrlGeneratorTrait;

    protected function setupClient(HttpClientInterface $httpClient)
    {
        return new DiscordTokenClient($httpClient, new DiscordRetryStrategy(), $this->validator, $this->serializer, $this->urlGenerator, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT);
    }
}
