<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\HttpClient\Common\HttpClient\QueryScopingHttpClient;
use Bytes\ResponseBundle\Enums\HttpMethods;
use Illuminate\Support\Arr;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Exception\ValidatorException;
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

    const PREVENTATIVE_RATE_LIMIT_SECONDS = 2;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * DiscordClient constructor.
     * @param HttpClientInterface $httpClient
     * @param DiscordRetryStrategy $strategy
     * @param string $clientId
     * @param string $clientSecret
     * @param string $botToken
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, DiscordRetryStrategy $strategy, ValidatorInterface $validator, SerializerInterface $serializer, string $clientId, string $clientSecret, string $botToken, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
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
                    'Authorization' => 'Bot ' . $botToken,
                ]),
            ],

            // Matches OAuth token revoke API routes
            'https://discord\.com/api(|/v6|/v8)/oauth2/token/revoke' => [
                'headers' => $headers,
                'query' => [
                    'client_id' => $clientId,
                ]
            ],
            // Matches OAuth token API routes
            'https://discord\.com/api(|/v6|/v8)/oauth2/token' => [
                'headers' => $headers,
                'query' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
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
        $this->clientId = $clientId;
        $this->validator = $validator;
        $this->serializer = $serializer;
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
     * @param string $version
     * @return string
     */
    protected function buildURL(string $path, string $version = 'v8')
    {
        $url = u($path);
        if(!empty($version))
        {
            $url = $url->ensureStart($version . '/');
        }
        return $url->ensureStart('https://discord.com/api/')->toString();
    }

    /**
     * @param ApplicationCommand $applicationCommand
     * @param IdInterface|null $guild
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function slashCreateCommand(ApplicationCommand $applicationCommand, ?IdInterface $guild = null)
    {

        $errors = $this->validator->validate($applicationCommand);
        if (count($errors) > 0) {
            throw new ValidatorException((string)$errors);
        }

        $urlParts = ['applications', $this->clientId];

        if(!empty($guild))
        {
            $urlParts[] = 'guilds';
            $urlParts[] = $guild->getId();
        }
        $urlParts[] = 'commands';

        $body = $this->serializer->serialize($applicationCommand, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        return $this->request($urlParts, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
        ], HttpMethods::post());
    }
}