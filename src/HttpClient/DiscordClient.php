<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\HttpClient\Common\HttpClient\ConfigurableScopingHttpClient;
use Bytes\ResponseBundle\Enums\HttpMethods;
use Bytes\ResponseBundle\Enums\OAuthGrantTypes;
use InvalidArgumentException;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function Symfony\Component\String\u;

/**
 * Class DiscordClient
 * @package Bytes\DiscordBundle\HttpClient
 */
class DiscordClient
{
    /**
     *
     */
    const PREVENTATIVE_RATE_LIMIT_SECONDS = 2;

    /**
     * Matches Slash Command API routes
     */
    const SCOPE_SLASH_COMMAND = 'https://discord\.com/api/v8/applications';

    /**
     * Matches OAuth token revoke API routes
     */
    const SCOPE_OAUTH_TOKEN_REVOKE = 'https://discord\.com/api(|/v6|/v8)/oauth2/token/revoke';

    /**
     * Matches OAuth token API routes
     */
    const SCOPE_OAUTH_TOKEN = 'https://discord\.com/api(|/v6|/v8)/oauth2/token';

    /**
     * Matches OAuth API routes (though there shouldn't be any...)
     */
    const SCOPE_OAUTH = 'https://discord\.com/api(|/v6|/v8)/oauth2';

    /**
     * Matches non-oauth API routes
     */
    const SCOPE_API = 'https://discord\.com/api(|/v6|/v8)/((?!oauth2).)';

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * DiscordClient constructor.
     * @param HttpClientInterface $httpClient
     * @param RetryStrategyInterface|null $strategy
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param string $clientId
     * @param string $clientSecret
     * @param string $botToken
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, ?RetryStrategyInterface $strategy, ValidatorInterface $validator, SerializerInterface $serializer, string $clientId, string $clientSecret, string $botToken, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $headers = [];
        if (!empty($userAgent)) {
            $headers['User-Agent'] = $userAgent;
        }
        $this->httpClient = new RetryableHttpClient(new ConfigurableScopingHttpClient($httpClient, array_merge_recursive([
            // the options defined as values apply only to the URLs matching
            // the regular expressions defined as keys

            // Matches Slash Command API routes
            self::SCOPE_SLASH_COMMAND => [
                'headers' => array_merge($headers, [
                    'Authorization' => 'Bot ' . $botToken,
                ]),
            ],

            // Matches OAuth token revoke API routes
            self::SCOPE_OAUTH_TOKEN_REVOKE => [
                'headers' => $headers,
                'query' => [
                    'client_id' => $clientId,
                ]
            ],
            // Matches OAuth token API routes
            self::SCOPE_OAUTH_TOKEN => [
                'headers' => $headers,
                'body' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ]
            ],
            // Matches OAuth API routes (though there shouldn't be any...)
            self::SCOPE_OAUTH => [
                'headers' => $headers,
            ],

            // Matches non-oauth API routes
            self::SCOPE_API => [
                'headers' => $headers,
            ],
        ], $defaultOptionsByRegexp), ['query', 'body'], $defaultRegexp), $strategy);
        $this->clientId = $clientId;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Get Current User Guilds
     * Returns a list of partial guild objects the current user is a member of. Requires the guilds OAuth2 scope.
     * This endpoint returns 100 guilds by default, which is the maximum number of guilds a non-bot user can join.
     * Therefore, pagination is not needed for integrations that need to get a list of the users' guilds.
     *
     * @return ResponseInterface
     *
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-current-user-guilds
     */
    public function getGuilds(): ResponseInterface
    {
        return $this->request($this->buildURL('users/@me/guilds', 'v6'));
    }

    /**
     * Get Current User
     * Returns the user object of the requester's account. For OAuth2, this requires the identify scope, which will
     * return the object without an email, and optionally the email scope, which returns the object with an email.
     *
     * @return ResponseInterface
     *
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-current-user
     */
    public function getMe()
    {
        return $this->request(['users', '@me']);
    }

    /**
     * Get User
     * Returns a user object for a given user ID.
     * @param IdInterface|string $userId
     *
     * @return ResponseInterface
     *
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-user
     *
     * @internal getUser is not available in DiscordUserClient
     */
    public function getUser($userId)
    {
        $userId = $this->normalizeIdArgument($userId, 'The "userId" argument is required.');
        $urlParts = ['users', $userId];
        return $this->request($urlParts);
    }

    /**
     * @param string|string[] $url
     * @param array $options = HttpClientInterface::OPTIONS_DEFAULTS
     * @param string $method = ['GET','HEAD','POST','PUT','DELETE','CONNECT','OPTIONS','TRACE','PATCH'][$any]
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function request($url, array $options = [], string $method = 'GET')
    {
        if (is_array($url)) {
            $url = implode('/', $url);
        }
        if (empty($url) || !is_string($url)) {
            throw new InvalidArgumentException();
        }
        $auth = $this->getAuthenticationOption();
        if (!empty($auth) && is_array($auth)) {
            $options = array_merge_recursive($options, $auth);
        }
        return $this->httpClient->request($method, $this->buildURL($url), $options);
    }

    /**
     * @return array
     */
    protected function getAuthenticationOption()
    {
        return [];
    }

    /**
     * @param string $path
     * @param string $version
     * @return string
     */
    protected function buildURL(string $path, string $version = 'v8')
    {
        $url = u($path);
        if ($url->startsWith('https://discord.com/api/')) {
            return $path;
        }
        if (!empty($version)) {
            $url = $url->ensureStart($version . '/');
        }
        return $url->ensureStart('https://discord.com/api/')->toString();
    }

    /**
     * @param string $code
     * @param string $redirect
     * @param array $scopes
     * @param OAuthGrantTypes|null $grantType
     * @return Token|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function tokenExchange(string $code, string $redirect, array $scopes = [], OAuthGrantTypes $grantType = null)
    {
        if (empty($scopes)) {
            $scopes = OAuthScopes::getBotScopes();
        }
        if (empty($grantType)) {
            $grantType = OAuthGrantTypes::authorizationCode();
        }
        $body = [
            'grant_type' => $grantType->value,
            'redirect_uri' => $redirect,
            'scope' => OAuthScopes::buildOAuthString($scopes),
        ];
        switch ($grantType) {
            case OAuthGrantTypes::authorizationCode():
                $body['code'] = $code;
                break;
            case OAuthGrantTypes::refreshToken():
                $body['refresh_token'] = $code;
                break;
        }
        $response = $this->request($this->buildURL('oauth2/token', ''),
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $body,
            ], HttpMethods::post());

        $json = $response->getContent();

        return $this->serializer->deserialize($json, Token::class, 'json');
    }

    /**
     * @param IdInterface|string $object
     * @param string $message
     * @return string
     * @internal
     */
    protected function normalizeIdArgument($object, string $message = '')
    {
        if(empty($message))
        {
            if(is_object($object))
            {
                $message = sprintf('The "%s" argument is required.', get_class($object));
            } else {
                $message = 'The argument is required.';
            }
        }
        $id = '';
        if (is_null($object)) {
            throw new BadRequestHttpException($message);
        }
        if ($object instanceof IdInterface) {
            $id = $object->getId();
        } elseif (is_string($object)) {
            $id = $object;
        }
        if (empty($id)) {
            throw new BadRequestHttpException($message);
        }
        return $id;
    }

}