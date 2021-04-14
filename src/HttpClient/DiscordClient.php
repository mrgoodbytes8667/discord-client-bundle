<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Objects\Embed\Embed;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\DiscordResponseBundle\Objects\Message\AllowedMentions;
use Bytes\DiscordResponseBundle\Objects\Message\WebhookContent;
use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\DiscordResponseBundle\Objects\User;
use Bytes\DiscordResponseBundle\Services\IdNormalizer;
use Bytes\HttpClient\Common\HttpClient\ConfigurableScopingHttpClient;
use Bytes\ResponseBundle\Enums\HttpMethods;
use Bytes\ResponseBundle\Enums\OAuthGrantTypes;
use Bytes\ResponseBundle\Interfaces\ClientResponseInterface;
use InvalidArgumentException;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\u;

/**
 * Class DiscordClient
 * @package Bytes\DiscordBundle\HttpClient
 */
class DiscordClient implements SerializerAwareInterface
{
    use SerializerAwareTrait;

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

    const ENDPOINT_CHANNEL = 'channels';
    const ENDPOINT_GUILD = 'guilds';
    const ENDPOINT_MESSAGE = 'messages';
    const ENDPOINT_MEMBER = 'members';
    const ENDPOINT_USER = 'users';
    const USER_ME = '@me';
    const ENDPOINT_WEBHOOK = 'webhooks';

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var DiscordResponse
     */
    protected $response;

    /**
     * DiscordClient constructor.
     * @param HttpClientInterface $httpClient
     * @param RetryStrategyInterface|null $strategy
     * @param string $clientId
     * @param string $clientSecret
     * @param string $botToken
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(protected HttpClientInterface $httpClient, ?RetryStrategyInterface $strategy, protected string $clientId, string $clientSecret, string $botToken, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
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
    }

    /**
     * Get Current User Guilds
     * Returns a list of partial guild objects the current user is a member of. Requires the guilds OAuth2 scope.
     * This endpoint returns 100 guilds by default, which is the maximum number of guilds a non-bot user can join.
     * Therefore, pagination is not needed for integrations that need to get a list of the users' guilds.
     *
     * @return DiscordResponse
     *
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-current-user-guilds
     */
    public function getGuilds(): DiscordResponse
    {
        return $this->request($this->buildURL('users/@me/guilds', 'v6'), '\Bytes\DiscordResponseBundle\Objects\PartialGuild[]');
    }

    /**
     * @param string|string[] $url
     * @param string|null $type
     * @param array $options = HttpClientInterface::OPTIONS_DEFAULTS
     * @param string $method = ['GET','HEAD','POST','PUT','DELETE','CONNECT','OPTIONS','TRACE','PATCH'][$any]
     * @param ClientResponseInterface|string|null $responseClass
     * @return ClientResponseInterface
     * @throws TransportExceptionInterface
     */
    public function request($url, ?string $type = null, array $options = [], $method = 'GET', ClientResponseInterface|string|null $responseClass = null)
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
        if(!is_null($responseClass))
        {
            if(is_string($responseClass) && is_subclass_of($responseClass, ClientResponseInterface::class)) {
                $response = $responseClass::makeFrom($this->response);
            } else {
                $response = $responseClass;
            }
        } else {
            $response = $this->response;
        }
        return $response->withResponse($this->httpClient->request($method, $this->buildURL($url), $options), $type);
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
     * Get Current User
     * Returns the user object of the requester's account. For OAuth2, this requires the identify scope, which will
     * return the object without an email, and optionally the email scope, which returns the object with an email.
     *
     * @return DiscordResponse
     *
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-current-user
     */
    public function getMe(): DiscordResponse
    {
        return $this->request([self::ENDPOINT_USER, self::USER_ME], User::class);
    }

    /**
     * Get User
     * Returns a user object for a given user ID.
     * @param IdInterface|string $userId
     *
     * @return DiscordResponse
     *
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-user
     *
     * @internal getUser is not available in DiscordUserClient
     */
    public function getUser($userId): DiscordResponse
    {
        $userId = IdNormalizer::normalizeIdArgument($userId, 'The "userId" argument is required.');
        $urlParts = [self::ENDPOINT_USER, $userId];
        return $this->request($urlParts, User::class);
    }

    /**
     * Execute Webhook
     * @param IdInterface|string $id Webhook Id
     * @param string $token Webhook token
     * @param bool $wait True will return a Message object, false does not
     * @param WebhookContent|string $content the message contents (up to 2000 characters)
     * @param Embed[]|Embed|null $embeds
     * @param AllowedMentions|null $allowedMentions
     * @param string|null $username
     * @param string|null $avatarUrl
     * @param bool|null $tts true if this is a TTS message
     * @return DiscordResponse
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/webhook#execute-webhook
     */
    public function executeWebhook($id, $token, bool $wait = true, $content = null, $embeds = [], ?AllowedMentions $allowedMentions = null, ?string $username = null, ?string $avatarUrl = null, ?bool $tts = null): DiscordResponse
    {
        return $this->sendWebhookPayload($id, $token, HttpMethods::post(), $wait, null, $content, $embeds, $allowedMentions, $username, $avatarUrl, $tts);
    }

    /**
     * @param IdInterface|string $id Webhook Id or Application Id
     * @param string $token Webhook token or Interaction token
     * @param HttpMethods $method = [HttpMethods::post(), HttpMethods::patch()][$any]
     * @param bool $wait True will return a Message object, false does not
     * @param IdInterface|string|null $messageId Message Id if editing an existing message
     * @param WebhookContent|string $content the message contents (up to 2000 characters)
     * @param Embed[]|Embed|null $embeds
     * @param AllowedMentions|null $allowedMentions
     * @param string|null $username
     * @param string|null $avatarUrl
     * @param bool $tts true if this is a TTS message
     * @return DiscordResponse
     *
     * @throws TransportExceptionInterface
     * @internal
     */
    protected function sendWebhookPayload($id, $token, HttpMethods $method, bool $wait = true, $messageId = null, $content = null, $embeds = [], ?AllowedMentions $allowedMentions = null, ?string $username = null, ?string $avatarUrl = null, ?bool $tts = null): DiscordResponse
    {
        $id = IdNormalizer::normalizeIdArgument($id, 'The "id" argument is required.');
        $urlParts = [self::ENDPOINT_WEBHOOK, $id, $token];
        if (!empty($messageId)) {
            $messageId = IdNormalizer::normalizeIdArgument($messageId, 'The "messageId" argument must be null or a valid id.');
            $urlParts[] = self::ENDPOINT_MESSAGE;
            $urlParts[] = $messageId;
        }

        if (!($content instanceof WebhookContent)) {
            $data = WebhookContent::create($embeds, is_string($content) ? $content : null, $allowedMentions, $username, $avatarUrl, $tts);
        } else {
            $data = $content;
        }

        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            throw new ValidatorException((string)$errors);
        }

        $body = $this->serializer->serialize($data, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
        ]);

        if ($method === HttpMethods::patch() && !empty($messageId)) {
            return $this->request($urlParts, type: Message::class, options: [
                'body' => $body
            ], method: $method);
        } elseif ($wait) {
            return $this->request($urlParts, type: Message::class, options: [
                'body' => $body,
                'query' => [
                    'wait' => $wait
                ]
            ], method: $method);
        } else {
            return $this->request($urlParts, options: [
                'body' => $body,
                'query' => [
                    'wait' => $wait
                ]
            ], method: $method);
        }
    }

    /**
     * Edit Webhook Message
     * Edits a previously-sent webhook message from the same token. Returns a message object on success.
     * @param IdInterface|string $id Webhook Id
     * @param string $token Webhook token
     * @param IdInterface|string $messageId Message Id to edit
     * @param WebhookContent|string $content the message contents (up to 2000 characters)
     * @param Embed[]|Embed|null $embeds
     * @param AllowedMentions|null $allowedMentions
     * @param string|null $username
     * @param string|null $avatarUrl
     * @param bool|null $tts true if this is a TTS message
     * @return DiscordResponse
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/webhook#edit-webhook-message
     */
    public function editWebhookMessage($id, $token, $messageId, $content = null, $embeds = [], ?AllowedMentions $allowedMentions = null, ?string $username = null, ?string $avatarUrl = null, ?bool $tts = null): DiscordResponse
    {
        return $this->sendWebhookPayload($id, $token, HttpMethods::patch(), true, $messageId, $content, $embeds, $allowedMentions, $username, $avatarUrl, $tts);
    }

    /**
     * Delete Webhook Message
     * Deletes a message that was created by the webhook. Returns a 204 NO CONTENT response on success.
     * @param IdInterface|string $id Webhook Id
     * @param string $token Webhook token
     * @param IdInterface|string $messageId Message Id to delete
     * @return DiscordResponse
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/webhook#delete-webhook-message
     */
    public function deleteWebhookMessage($id, $token, $messageId)
    {
        $id = IdNormalizer::normalizeIdArgument($id, 'The "id" argument is required and cannot be blank.');
        $messageId = IdNormalizer::normalizeIdArgument($messageId, 'The "messageId" argument is required and cannot be blank.');

        return $this->request(url: [self::ENDPOINT_WEBHOOK, $id, $token, self::ENDPOINT_MESSAGE, $messageId], method: HttpMethods::delete());
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

        return $this->request($this->buildURL('oauth2/token', ''),
            Token::class,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $body,
            ], HttpMethods::post())
            ->deserialize();
    }

    /**
     * @param ValidatorInterface $validator
     * @return $this
     */
    public function setValidator(ValidatorInterface $validator): self
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @param DiscordResponse $response
     * @return $this
     */
    public function setResponse(DiscordResponse $response): self
    {
        $this->response = $response;
        return $this;
    }
}