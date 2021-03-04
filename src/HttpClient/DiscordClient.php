<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\HttpClient\Common\HttpClient\QueryScopingHttpClient;
use InvalidArgumentException;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
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
        $this->httpClient = new RetryableHttpClient(new QueryScopingHttpClient($httpClient, array_merge_recursive([
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
                'query' => [
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
        ], $defaultOptionsByRegexp), $defaultRegexp), $strategy);
        $this->clientId = $clientId;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Get Current User Guilds
     * Returns a list of partial guild objects the current user is a member of. Requires the guilds OAuth2 scope.
     * This endpoint returns 100 guilds by default, which is the maximum number of guilds a non-bot user can join. Therefore, pagination is not needed for integrations that need to get a list of the users' guilds.
     *
     * @return PartialGuild[]|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-current-user-guilds
     */
    public function getGuilds()
    {
        $response = $this->request(['users', '@me', 'guilds']);

        $content = $response->getContent();

        return $this->serializer->deserialize($content, '\Bytes\DiscordResponseBundle\Objects\PartialGuild[]', 'json');
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
        if (!empty($version)) {
            $url = $url->ensureStart($version . '/');
        }
        return $url->ensureStart('https://discord.com/api/')->toString();
    }

}