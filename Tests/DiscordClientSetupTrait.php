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
use Bytes\DiscordBundle\Tests\MockHttpClient\MockClient;
use Bytes\ResponseBundle\HttpClient\Response\Response;
use Bytes\ResponseBundle\HttpClient\Response\TokenResponse;
use Bytes\ResponseBundle\HttpClient\Retry\APIRetryStrategy;
use Bytes\Tests\Common\TestFullSerializerTrait;
use Bytes\Tests\Common\TestFullValidatorTrait;
use Bytes\TwitchClientBundle\HttpClient\Api\TwitchClient;
use Bytes\TwitchResponseBundle\Objects\OAuth2\Token;
use Doctrine\Common\Annotations\AnnotationReader;
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
     * @param HttpClientInterface|null $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return \Bytes\DiscordBundle\HttpClient\Api\DiscordClient
     */
    protected function setupBaseClient(HttpClientInterface $httpClient = null, ?EventDispatcher $dispatcher = null, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordClient($httpClient ?? MockClient::empty(), $dispatcher ?? new EventDispatcher(), new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client, $dispatcher ?? new EventDispatcher());
    }

    /**
     * @param HttpClientInterface|null $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordBotClient
     */
    protected function setupBotClient(HttpClientInterface $httpClient = null, ?EventDispatcher $dispatcher = null, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordBotClient($httpClient ?? MockClient::empty(), $dispatcher ?? new EventDispatcher(), new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client, $dispatcher ?? new EventDispatcher());
    }

    /**
     * @param HttpClientInterface|null $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordUserClient
     */
    protected function setupUserClient(HttpClientInterface $httpClient = null, ?EventDispatcher $dispatcher = null, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = $this->getMockBuilder(DiscordUserClient::class)
        ->setConstructorArgs([$httpClient ?? MockClient::empty(), $dispatcher ?? new EventDispatcher(), new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT, $defaultOptionsByRegexp, $defaultRegexp])
        ->onlyMethods(['getToken'])
        ->getMock();
        $client->method('getToken')
            ->willReturn(\Bytes\DiscordResponseBundle\Objects\Token::createFromAccessToken(Fixture::BOT_TOKEN));
        return $this->postClientSetup($client, $dispatcher ?? new EventDispatcher());
    }

    /**
     * @param HttpClientInterface|null $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordUserClient
     */
    protected function setupRealUserClient(HttpClientInterface $httpClient = null, ?EventDispatcher $dispatcher = null, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordUserClient($httpClient ?? MockClient::empty(), $dispatcher ?? new EventDispatcher(), new DiscordRetryStrategy(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client, $dispatcher ?? new EventDispatcher());
    }

    /**
     * @param HttpClientInterface|null $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordBotTokenClient
     */
    protected function setupBotTokenClient(HttpClientInterface $httpClient = null, ?EventDispatcher $dispatcher = null, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordBotTokenClient($httpClient ?? MockClient::empty(), $dispatcher ?? new EventDispatcher(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::BOT_TOKEN, Fixture::USER_AGENT, false, true, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client, $dispatcher ?? new EventDispatcher(), TokenResponse::class);
    }

    /**
     * @param HttpClientInterface|null $httpClient
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     * @return DiscordUserTokenClient
     */
    protected function setupUserTokenClient(HttpClientInterface $httpClient = null, ?EventDispatcher $dispatcher = null, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $client = new DiscordUserTokenClient($httpClient ?? MockClient::empty(), $dispatcher ?? new EventDispatcher(), Fixture::CLIENT_ID, Fixture::CLIENT_SECRET, Fixture::USER_AGENT, false, true, $defaultOptionsByRegexp, $defaultRegexp);
        return $this->postClientSetup($client, $dispatcher ?? new EventDispatcher(), DiscordUserTokenResponse::class);
    }

    /**
     * @param \Bytes\DiscordBundle\HttpClient\Api\DiscordClient|DiscordBotClient|DiscordUserClient|DiscordBotTokenClient|DiscordUserTokenClient $client
     * @return DiscordClient|DiscordBotClient|DiscordUserClient|DiscordBotTokenClient|DiscordUserTokenClient
     */
    private function postClientSetup($client, ?EventDispatcher $dispatcher = null, $responseClass = Response::class)
    {
        $client->setSerializer($this->serializer);
        $client->setValidator($this->validator);
        $client->setReader(new AnnotationReader());
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
