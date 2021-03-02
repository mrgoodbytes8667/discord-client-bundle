<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\HttpClient\Common\HttpClient\QueryScopingHttpClient;
use Illuminate\Support\Arr;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
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

    const PREVENTATIVE_RATE_LIMIT_SECONDS = 2;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * DiscordClient constructor.
     * @param HttpClientInterface $httpClient
     * @param DiscordRetryStrategy $strategy
     * @param string $discordClientID
     * @param string $discordClientSecret
     * @param string $discordBotToken
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, DiscordRetryStrategy $strategy, string $discordClientID, string $discordClientSecret, string $discordBotToken, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $headers = [];
        if(!empty($userAgent))
        {
            $headers['User-Agent'] = $userAgent;
        }
        $this->httpClient = new RetryableHttpClient(new QueryScopingHttpClient($httpClient, array_merge([
            // the options defined as values apply only to the URLs matching
            // the regular expressions defined as keys

            // Matches Slash Command API routes
            'https://discord\.com/api/v8/applications' => [
                'headers' => array_merge($headers, [
                    'Authorization' => 'Bot ' . $discordBotToken,
                ]),
            ],

            // Matches OAuth token revoke API routes
            'https://discord\.com/api(|/v6|/v8)/oauth2/token/revoke' => [
                'headers' => $headers,
                'query' => [
                    'client_id' => $discordClientID,
                ]
            ],
            // Matches OAuth token API routes
            'https://discord\.com/api(|/v6|/v8)/oauth2/token' => [
                'headers' => $headers,
                'query' => [
                    'client_id' => $discordClientID,
                    'client_secret' => $discordClientSecret,
                ]
            ],
            // Matches OAuth API routes (though there shouldn't be any...)
            'https://discord\.com/api(|/v6|/v8)/oauth2' => [
                'headers' => $headers,
            ],

            // Matches non-oauth API routes
            'https://discord\.com/api(|/v6|/v8)/((?!oauth2).)' => [
                'headers' => $headers,
            ],
        ], $defaultOptionsByRegexp), $defaultRegexp), $strategy);
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
            throw new \InvalidArgumentException();
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
     * @return string
     */
    protected function buildURL(string $path)
    {
        return u($path)->ensureStart('https://discord.com/api/v8/')->toString();
    }
}