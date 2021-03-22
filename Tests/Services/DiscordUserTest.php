<?php

namespace Bytes\DiscordBundle\Tests\Services;

use Bytes\DiscordBundle\HttpClient\DiscordUserClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Services\Client\DiscordUser;
use Bytes\DiscordBundle\Tests\CommandProviderTrait;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordUserTest
 * @package Bytes\DiscordBundle\Tests\Services
 */
class DiscordUserTest extends TestCase
{
    use TestFullValidatorTrait, TestFullSerializerTrait, TestDiscordTrait, CommandProviderTrait;

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordUser
     */
    protected function setupClient(HttpClientInterface $httpClient)
    {
        $client = new DiscordUserClient($httpClient, new DiscordRetryStrategy(), $this->validator, $this->serializer, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT);
        return new DiscordUser($client, $this->serializer);
    }
}
