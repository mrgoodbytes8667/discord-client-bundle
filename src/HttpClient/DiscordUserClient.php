<?php


namespace Bytes\DiscordBundle\HttpClient;


use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordUserClient
 * @package Bytes\DiscordBundle\HttpClient
 */
class DiscordUserClient extends DiscordClient
{
    /**
     * DiscordUserClient constructor.
     * @param HttpClientInterface $httpClient
     * @param RetryStrategyInterface|null $strategy
     * @param string $clientId
     * @param string $clientSecret
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, ?RetryStrategyInterface $strategy, string $clientId, string $clientSecret, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        parent::__construct($httpClient, $strategy, $clientId, $clientSecret, '', $userAgent, $defaultOptionsByRegexp, $defaultRegexp);
    }
}
