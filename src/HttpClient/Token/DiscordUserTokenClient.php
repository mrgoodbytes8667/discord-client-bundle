<?php


namespace Bytes\DiscordClientBundle\HttpClient\Token;


use Bytes\DiscordClientBundle\HttpClient\DiscordClientEndpoints;
use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Objects\OAuth\Validate\Bot;
use Bytes\DiscordResponseBundle\Objects\OAuth\Validate\User;
use Bytes\ResponseBundle\Annotations\Client;
use Bytes\ResponseBundle\Enums\HttpMethods;
use Bytes\ResponseBundle\Enums\OAuthGrantTypes;
use Bytes\ResponseBundle\Event\RevokeTokenEvent;
use Bytes\ResponseBundle\Event\TokenGrantedEvent;
use Bytes\ResponseBundle\Event\TokenRefreshedEvent;
use Bytes\ResponseBundle\Event\TokenRevokedEvent;
use Bytes\ResponseBundle\Event\TokenValidatedEvent;
use Bytes\ResponseBundle\HttpClient\Token\AbstractTokenClient;
use Bytes\ResponseBundle\HttpClient\Token\UserTokenClientInterface;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;
use Bytes\ResponseBundle\Objects\Push;
use Bytes\ResponseBundle\Token\Exceptions\NoTokenException;
use Bytes\ResponseBundle\Token\Exceptions\TokenRevokeException;
use Bytes\ResponseBundle\Token\Interfaces\AccessTokenInterface;
use Bytes\ResponseBundle\Token\Interfaces\TokenValidationResponseInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

/**
 * Class DiscordUserTokenClient
 * @package Bytes\DiscordClientBundle\HttpClient\Token
 *
 * @Client(identifier="DISCORD", tokenSource="user")
 */
class DiscordUserTokenClient extends AbstractDiscordTokenClient implements UserTokenClientInterface
{
    /**
     * Overloadable method to setup the revoke/fire on refresh variables
     * @param bool $revokeOnRefresh
     * @param bool $fireRevokeOnRefresh
     * @return $this
     */
    public function setupRevokeOnRefresh(bool $revokeOnRefresh, bool $fireRevokeOnRefresh): self
    {
        // Discord auto-revokes the original token. Force the event to fire.
        $this->revokeOnRefresh = false;
        $this->fireRevokeOnRefresh = true;

        return $this;
    }

    /**
     * Revokes the provided access token
     * @param AccessTokenInterface $token
     * @return ClientResponseInterface
     *
     * @throws TransportExceptionInterface
     * @throws UnexpectedValueException
     */
    public function revokeToken(AccessTokenInterface $token): ClientResponseInterface
    {
        $tokenString = static::normalizeAccessToken($token, false, 'The $token argument is required and cannot be empty.');

        $response = $this->request($this->buildURL('oauth2/revoke'), options: ['body' => [
            'token' => $tokenString
        ]], method: HttpMethods::post(), onSuccessCallable: function ($self, $results) use ($token) {
            $this->dispatch(TokenRevokedEvent::new($token));
        });
        if(!$response->isSuccess())
        {
            throw new TokenRevokeException($response->getResponse(), sprintf('Could not revoke token: %d', $response->getStatusCode()));
        }
        return $response->onSuccessCallback();
    }

    /**
     * Refreshes the provided access token
     * @param AccessTokenInterface|null $token
     * @return AccessTokenInterface|null
     */
    public function refreshToken(AccessTokenInterface $token = null): ?AccessTokenInterface
    {
        $tokenString = static::normalizeRefreshToken($token);
        if (empty($tokenString)) {
            return null;
        }
        $redirect = $this->oAuth->getRedirect();
        return $this->tokenExchange($tokenString, url: $redirect, scopes: OAuthScopes::getUserScopes(), grantType: OAuthGrantTypes::refreshToken(),
            onDeserializeCallable: function ($self, $results) use ($token) {
                /** @var ClientResponseInterface $self */
                /** @var AccessTokenInterface|null $results */

                /** @var TokenRefreshedEvent $event */
                $event = $this->dispatch(TokenRefreshedEvent::new($results, $token));

                if($this->revokeOnRefresh) {
                    $this->dispatch(RevokeTokenEvent::new($token));
                } elseif($this->fireRevokeOnRefresh)
                {
                    $this->dispatch(TokenRevokedEvent::new($token));
                }

                return $event->getToken();
            })->deserialize();
    }

    /**
     * Exchanges the provided code (or token) for a (new) access token
     * @param string $code
     * @param string|null $route Either $route or $url (or setOAuth(()) is required, $route takes precedence over $url
     * @param string|null|callable(string, array) $url Either $route or $url (or setOAuth(()) is required, $route takes precedence over $url
     * @param array $scopes
     * @param callable(static, mixed)|null $onSuccessCallable If set, will be triggered if it returns successfully
     * @return AccessTokenInterface|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function exchange(string $code, ?string $route = null, string|callable|null $url = null, array $scopes = [], ?callable $onSuccessCallable = null): ?AccessTokenInterface
    {
        return $this->tokenExchange($code, $route, $url, $scopes, OAuthGrantTypes::authorizationCode(), onDeserializeCallable: function ($self, $results) {
            /** @var TokenGrantedEvent $event */
            $event = $this->dispatch(TokenGrantedEvent::new($results));
            return $event->getToken();
        }, onSuccessCallable: $onSuccessCallable)->deserialize();
    }


    /**
     * Validates the provided access token
     * Should fire a TokenValidatedEvent on success if $fireCallback is true
     * @param AccessTokenInterface $token
     * @param bool $fireCallback Should a TokenValidatedEvent be fired?
     * @return TokenValidationResponseInterface|null
     *
     * @see TokenValidatedEvent
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function validateToken(AccessTokenInterface $token, bool $fireCallback = false): ?TokenValidationResponseInterface
    {
        $tokenString = static::normalizeAccessToken($token, false, 'The $token argument is required and cannot be empty.');

        $response = $this->request(url: ['oauth2', DiscordClientEndpoints::USER_ME], type: User::class, options: [
            'auth_bearer' => $tokenString
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
     * Remove scope and redirect_uri from Discord token refreshes
     * @param Push $body
     * @return Push
     */
    protected function normalizeTokenExchangeBody(Push $body): Push
    {
        $grantType = $body->getValue('grant_type');
        if($grantType == 'refresh_token')
        {
            $body->removeKey('scope')
                ->removeKey('redirect_uri');
        }
        return $body;
    }

    /**
     * Return the client name
     * @return string
     */
    public static function getDefaultIndexName(): string
    {
        return 'DISCORD-TOKEN-USER';
    }
}