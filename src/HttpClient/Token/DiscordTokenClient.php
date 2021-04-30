<?php


namespace Bytes\DiscordBundle\HttpClient\Token;


use Bytes\DiscordBundle\HttpClient\DiscordClientEndpoints;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Enums\HttpMethods;
use Bytes\ResponseBundle\Event\EventDispatcherTrait;
use Bytes\ResponseBundle\Event\TokenRevokedEvent;
use Bytes\ResponseBundle\HttpClient\Token\AbstractTokenClient;
use Bytes\ResponseBundle\HttpClient\Token\AppTokenClientInterface;
use Bytes\ResponseBundle\HttpClient\Token\UserTokenClientInterface;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;
use Bytes\ResponseBundle\Objects\Push;
use Bytes\ResponseBundle\Token\Interfaces\AccessTokenInterface;
use Bytes\ResponseBundle\Token\Interfaces\TokenValidationResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordTokenClient
 * @package Bytes\DiscordBundle\HttpClient\Token
 */
class DiscordTokenClient extends AbstractTokenClient implements AppTokenClientInterface, UserTokenClientInterface
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

//                // Matches OAuth token revoke API routes
//                DiscordClientEndpoints::SCOPE_OAUTH_TOKEN_REVOKE => [
//                    'headers' => $headers,
//                    'body' => [
//                        'client_id' => $clientId,
//                        'client_secret' => $clientSecret,
//                    ]
//                ],

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

    /**
     * Refreshes the provided access token
     * @param AccessTokenInterface|null $token
     * @return AccessTokenInterface|null
     */
    public function refreshToken(AccessTokenInterface $token = null): ?AccessTokenInterface
    {
        // TODO: Implement refreshToken() method.
        return null;
    }

    /**
     * Revokes the provided access token
     * @param AccessTokenInterface $token
     * @return ClientResponseInterface
     *
     * @throws TransportExceptionInterface
     */
    public function revokeToken(AccessTokenInterface $token): ClientResponseInterface
    {
        $tokenString = static::normalizeAccessToken($token, false, 'The $token argument is required and cannot be empty.');

        return $this->request($this->buildURL('oauth2/revoke'), options: ['body' => [
            'token' => $tokenString
        ]], method: HttpMethods::post(), onSuccessCallable: function ($self, $results) use ($token) {
            $this->dispatcher->dispatch(TokenRevokedEvent::new($token), TokenRevokedEvent::NAME);
        });
    }

    /**
     * Validates the provided access token
     * Should fire a TokenValidatedEvent on success
     * @param AccessTokenInterface $token
     * @return TokenValidationResponseInterface|null
     *
     * @see TokenValidatedEvent
     */
    public function validateToken(AccessTokenInterface $token): ?TokenValidationResponseInterface
    {
        // TODO: Implement validateToken() method.
        return null;
    }

    /**
     * Returns an access token
     * @return AccessTokenInterface|null
     */
    public function getToken(): ?AccessTokenInterface
    {
        // TODO: Implement getToken() method.
        return null;
    }
}