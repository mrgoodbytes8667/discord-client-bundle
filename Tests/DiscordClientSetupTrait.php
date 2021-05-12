<?php


namespace Bytes\DiscordBundle\Tests;


use Bytes\DiscordBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Api\DiscordClient;
use Bytes\DiscordBundle\HttpClient\Api\DiscordUserClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\HttpClient\Token\AbstractDiscordTokenClient;
use Bytes\DiscordBundle\HttpClient\Token\DiscordBotTokenClient;
use Bytes\DiscordBundle\HttpClient\Token\DiscordUserTokenClient;
use Bytes\DiscordBundle\HttpClient\Token\DiscordUserTokenResponse;
use Bytes\DiscordBundle\Tests\Fixtures\Fixture;
use Bytes\ResponseBundle\HttpClient\Response\Response;
use Bytes\ResponseBundle\HttpClient\Response\TokenResponse;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
    use TestFullValidatorTrait, TestFullSerializerTrait, TestUrlGeneratorTrait;

    /**
     * @param HttpClientInterface $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return \Bytes\DiscordBundle\HttpClient\Api\DiscordClient
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
     * @return DiscordBotTokenClient
     */
    protected function setupBotTokenClient(HttpClientInterface $httpClient, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordBotTokenClient($httpClient, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT, false, true, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client, TokenResponse::class);
    }

    /**
     * @param HttpClientInterface $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordUserTokenClient
     */
    protected function setupUserTokenClient(HttpClientInterface $httpClient, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordUserTokenClient($httpClient, Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT, false, true, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client, DiscordUserTokenResponse::class);
    }

    /**
     * @param \Bytes\DiscordBundle\HttpClient\Api\DiscordClient|DiscordBotClient|DiscordUserClient|DiscordBotTokenClient|DiscordUserTokenClient $client
     * @return DiscordClient|DiscordBotClient|DiscordUserClient|DiscordBotTokenClient|DiscordUserTokenClient
     */
    private function postClientSetup($client, $responseClass = Response::class)
    {
        $client->setSerializer($this->serializer);
        $client->setValidator($this->validator);
        if(method_exists($client, 'setDispatcher'))
        {
            $client->setDispatcher($dispatcher ?? new EventDispatcher());
        }
        if(method_exists($client, 'setUrlGenerator'))
        {
            $client->setUrlGenerator($this->urlGenerator);
        }
        $client->setResponse($responseClass::make($this->serializer, $dispatcher ?? new EventDispatcher()));
        return $client;
    }
}
