<?php


namespace Bytes\DiscordBundle\HttpClient\Api;


use Bytes\ResponseBundle\Annotations\Client;
use Bytes\ResponseBundle\HttpClient\ApiAuthenticationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordUserClient
 * @package Bytes\DiscordBundle\HttpClient\Api
 *
 * @Client(identifier="DISCORD", tokenSource="user")
 */
class DiscordUserClient extends DiscordClient
{
    use ApiAuthenticationTrait;

    /**
     * DiscordUserClient constructor.
     * @param HttpClientInterface $httpClient
     * @param EventDispatcherInterface $dispatcher
     * @param RetryStrategyInterface|null $strategy
     * @param string $clientId
     * @param string $clientSecret
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, EventDispatcherInterface $dispatcher, ?RetryStrategyInterface $strategy, string $clientId, string $clientSecret, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        parent::__construct($httpClient, $dispatcher, $strategy, $clientId, $clientSecret, '', $userAgent, $defaultOptionsByRegexp, $defaultRegexp, true);
    }

    /**
     * Return the client name
     * @return string
     */
    public static function getDefaultIndexName(): string
    {
        return 'DISCORD-USER';
    }
}
