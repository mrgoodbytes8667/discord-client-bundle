<?php


namespace Bytes\DiscordClientBundle\HttpClient\Token;


use Bytes\DiscordClientBundle\HttpClient\DiscordClientEndpoints;
use Bytes\DiscordResponseBundle\Objects\OAuth\Validate\Bot;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Annotations\Client;
use Bytes\ResponseBundle\Event\TokenValidatedEvent;
use Bytes\ResponseBundle\HttpClient\Token\AppTokenClientInterface;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;
use Bytes\ResponseBundle\Token\Interfaces\AccessTokenInterface;
use Bytes\ResponseBundle\Token\Interfaces\TokenValidationResponseInterface;
use Exception;
use LogicException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordBotTokenClient
 * @package Bytes\DiscordClientBundle\HttpClient\Token
 *
 * @Client(identifier="DISCORD", tokenSource="app")
 */
class DiscordBotTokenClient extends AbstractDiscordTokenClient implements AppTokenClientInterface
{
    /**
     * DiscordBotTokenClient constructor.
     * @param HttpClientInterface $httpClient
     * @param EventDispatcherInterface $dispatcher
     * @param string $clientId
     * @param string $clientSecret
     * @param string $botToken
     * @param string|null $userAgent
     * @param bool $revokeOnRefresh
     * @param bool $fireRevokeOnRefresh
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, EventDispatcherInterface $dispatcher, string $clientId, string $clientSecret, private string $botToken, ?string $userAgent, bool $revokeOnRefresh, bool $fireRevokeOnRefresh, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        parent::__construct($httpClient, $dispatcher, $clientId, $clientSecret, $userAgent, $revokeOnRefresh, $fireRevokeOnRefresh, $defaultOptionsByRegexp, $defaultRegexp);
    }

    /**
     * Revokes the provided access token
     * @param AccessTokenInterface $token
     * @return ClientResponseInterface
     */
    public function revokeToken(AccessTokenInterface $token): ClientResponseInterface
    {
        throw new LogicException('Discord bot tokens cannot be revoked via the API');
    }

    /**
     * Refreshes the provided access token
     * @param AccessTokenInterface|null $token
     * @return AccessTokenInterface|null
     * @throws Exception
     */
    public function refreshToken(AccessTokenInterface $token = null): ?AccessTokenInterface
    {
        return $this->getToken();
    }

    /**
     * Returns an access token
     * @return AccessTokenInterface|null
     *
     * @throws Exception
     */
    public function getToken(): ?AccessTokenInterface
    {
        return Token::createFromAccessToken($this->botToken);
    }

    /**
     * Exchanges the provided code for a new access token
     * @param string $code
     * @param string|null $route Either $route or $url is required, $route takes precedence over $url
     * @param string|null|callable(string, array) $url Either $route or $url is required, $route takes precedence over $url
     * @param array $scopes
     * @param callable(static, mixed)|null $onSuccessCallable If set, will be triggered if it returns successfully
     * @return AccessTokenInterface|null
     *
     * @throws Exception
     */
    public function exchange(string $code, ?string $route = null, callable|string|null $url = null, array $scopes = [], ?callable $onSuccessCallable = null): ?AccessTokenInterface
    {
        return $this->getToken();
    }

    /**
     * Validates the provided access token
     * Should fire a TokenValidatedEvent on success if $fireCallback is true
     * @param AccessTokenInterface $token
     * @param bool $fireCallback Should a TokenValidatedEvent be fired?
     * @return TokenValidationResponseInterface|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @see TokenValidatedEvent
     *
     */
    public function validateToken(AccessTokenInterface $token, bool $fireCallback = false): ?TokenValidationResponseInterface
    {
        $tokenString = static::normalizeAccessToken($token, false, 'The $token argument is required and cannot be empty.');

        $response = $this->request(url: ['oauth2', 'applications', DiscordClientEndpoints::USER_ME], type: Bot::class, options: [
            'headers' => [
                'Authorization' => 'Bot ' . $tokenString
            ]
        ], onSuccessCallable: function ($self, $results) use ($fireCallback, $token) {
            if($fireCallback) {
                $this->dispatch(TokenValidatedEvent::new($token, $results));
            }
        });
        try {
            if ($response->isSuccess()) {
                return $response->deserialize();
            }
            if (empty($response->getContent(throw: false))) {
                return null;
            }
            return $response->deserialize(throw: false);
        } catch (NotEncodableValueException | NotNormalizableValueException) {
            return null;
        }
    }

    /**
     * Return the client name
     * @return string
     */
    public static function getDefaultIndexName(): string
    {
        return 'DISCORD-TOKEN-BOT';
    }
}