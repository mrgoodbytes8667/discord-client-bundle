<?php


namespace Bytes\DiscordBundle\HttpClient\Token;


use Bytes\DiscordBundle\HttpClient\DiscordClientEndpoints;
use Bytes\DiscordResponseBundle\Objects\OAuth\Validate\Bot;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Event\TokenValidatedEvent;
use Bytes\ResponseBundle\HttpClient\Token\AppTokenClientInterface;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;
use Bytes\ResponseBundle\Token\Interfaces\AccessTokenInterface;
use Bytes\ResponseBundle\Token\Interfaces\TokenValidationResponseInterface;
use Exception;
use LogicException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordBotTokenClient
 * @package Bytes\DiscordBundle\HttpClient\Token
 */
class DiscordBotTokenClient extends AbstractDiscordTokenClient implements AppTokenClientInterface
{
    /**
     * DiscordBotTokenClient constructor.
     * @param HttpClientInterface $httpClient
     * @param string $clientId
     * @param string $clientSecret
     * @param string $botToken
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, string $clientId, string $clientSecret, private string $botToken, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        parent::__construct($httpClient, $clientId, $clientSecret, $userAgent, $defaultOptionsByRegexp, $defaultRegexp);
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
     * Should fire a TokenValidatedEvent on success
     * @param AccessTokenInterface $token
     * @return TokenValidationResponseInterface|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @see TokenValidatedEvent
     *
     */
    public function validateToken(AccessTokenInterface $token): ?TokenValidationResponseInterface
    {
        $tokenString = static::normalizeAccessToken($token, false, 'The $token argument is required and cannot be empty.');

        $response = $this->request(url: ['oauth2', 'applications', DiscordClientEndpoints::USER_ME], type: Bot::class, options: [
            'headers' => [
                'Authorization' => 'Bot ' . $tokenString
            ]
        ], onSuccessCallable: function ($self, $results) use ($token) {
            $this->dispatcher->dispatch(TokenValidatedEvent::new($token, $results), TokenValidatedEvent::NAME);
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
}