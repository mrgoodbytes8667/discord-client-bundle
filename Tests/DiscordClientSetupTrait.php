<?php


namespace Bytes\DiscordBundle\Tests;


use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\DiscordClient;
use Bytes\DiscordBundle\HttpClient\DiscordTokenClient;
use Bytes\DiscordBundle\HttpClient\DiscordUserClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\ResponseBundle\HttpClient\Response\Response;
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
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordClient
     */
    protected function setupBaseClient(HttpClientInterface $httpClient, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordClient($httpClient, new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordBotClient
     */
    protected function setupBotClient(HttpClientInterface $httpClient, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordBotClient($httpClient, new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordUserClient
     */
    protected function setupUserClient(HttpClientInterface $httpClient, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordUserClient($httpClient, new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordTokenClient
     */
    protected function setupTokenClient(HttpClientInterface $httpClient, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordTokenClient($httpClient, new DiscordRetryStrategy(), $this->urlGenerator, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT, $defaultOptionsByRegexp, $defaultRegexp);
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
        $client->setResponse(Response::make($this->serializer));
        return $client;
    }
}
