<?php


namespace Bytes\DiscordBundle\Tests;


use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\DiscordClient;
use Bytes\DiscordBundle\HttpClient\DiscordTokenClient;
use Bytes\DiscordBundle\HttpClient\DiscordUserClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Trait DiscordClientSetupTrait
 * @package Bytes\DiscordBundle\Tests
 *
 * @property UrlGeneratorInterface $urlGenerator
 */
trait DiscordClientSetupTrait
{
    use TestFullValidatorTrait, TestFullSerializerTrait;

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordClient
     */
    protected function setupBaseClient(HttpClientInterface $httpClient)
    {
        $client = new DiscordClient($httpClient, new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT);
        $client->setSerializer($this->serializer);
        return $this->postClientSetup($client);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordBotClient
     */
    protected function setupBotClient(HttpClientInterface $httpClient)
    {
        $client = new DiscordBotClient($httpClient, new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT);
        $client->setSerializer($this->serializer);
        return $this->postClientSetup($client);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordUserClient
     */
    protected function setupUserClient(HttpClientInterface $httpClient)
    {
        $client = new DiscordUserClient($httpClient, new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT);
        $client->setSerializer($this->serializer);
        return $this->postClientSetup($client);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @return DiscordTokenClient
     */
    protected function setupTokenClient(HttpClientInterface $httpClient)
    {
        $client = new DiscordTokenClient($httpClient, new DiscordRetryStrategy(), $this->urlGenerator, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT);
        $client->setSerializer($this->serializer);
        return $this->postClientSetup($client);
    }

    /**
     * @param DiscordClient|DiscordBotClient|DiscordUserClient|DiscordTokenClient $client
     * @return DiscordClient|DiscordBotClient|DiscordUserClient|DiscordTokenClient
     */
    private function postClientSetup($client)
    {
        $client->setSerializer($this->serializer);
        $client->setValidator($this->validator);
        return $client;
    }
}
