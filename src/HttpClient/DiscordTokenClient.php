<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Enums\OAuthGrantTypes;
use Bytes\ResponseBundle\Event\EventDispatcherTrait;
use Bytes\ResponseBundle\HttpClient\Token\AbstractTokenClient;
use Bytes\ResponseBundle\Objects\Push;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordTokenClient
 * @package Bytes\DiscordBundle\HttpClient
 */
class DiscordTokenClient extends AbstractTokenClient
{
    use EventDispatcherTrait;

    /**
     * DiscordTokenClient constructor.
     * @param HttpClientInterface $httpClient
     * @param string $clientId
     * @param string $clientSecret
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, string $clientId, string $clientSecret, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $headers = Push::createPush(value: $userAgent, key: 'User-Agent')->value();
        parent::__construct($httpClient, $userAgent,
            array_merge_recursive([
                // the options defined as values apply only to the URLs matching
                // the regular expressions defined as keys

                // Matches OAuth token revoke API routes
                DiscordClientEndpoints::SCOPE_OAUTH_TOKEN_REVOKE => [
                    'headers' => $headers,
                    'query' => [
                        'client_id' => $clientId,
                    ]
                ],
                // Matches OAuth token API routes
                DiscordClientEndpoints::SCOPE_OAUTH_TOKEN => [
                    'headers' => $headers,
                    'body' => [
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                    ]
                ],
                // Matches OAuth API routes (though there shouldn't be any...)
                DiscordClientEndpoints::SCOPE_OAUTH => [
                    'headers' => $headers,
                ],
            ], $defaultOptionsByRegexp), $defaultRegexp);
    }

    /**
     * @return string|null
     */
    protected static function getTokenExchangeBaseUri()
    {
        return DiscordClientEndpoints::ENDPOINT_DISCORD_API;
    }

    /**
     * @return string|null
     */
    protected static function getTokenExchangeDeserializationClass()
    {
        return Token::class;
    }
}