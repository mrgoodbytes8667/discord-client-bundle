<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\DiscordResponseBundle\Objects\Token;
use Bytes\ResponseBundle\Enums\OAuthGrantTypes;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class DiscordTokenClient
 * @package Bytes\DiscordBundle\HttpClient
 */
class DiscordTokenClient extends DiscordClient
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * DiscordTokenClient constructor.
     * @param HttpClientInterface $httpClient
     * @param RetryStrategyInterface|null $strategy
     * @param ValidatorInterface $validator
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $clientId
     * @param string $clientSecret
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, ?RetryStrategyInterface $strategy, ValidatorInterface $validator, UrlGeneratorInterface $urlGenerator, string $clientId, string $clientSecret, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $this->urlGenerator = $urlGenerator;
        parent::__construct($httpClient, $strategy, $validator, $clientId, $clientSecret, '', $userAgent, $defaultOptionsByRegexp, $defaultRegexp);
    }

    /**
     * @param string $code
     * @param string $redirect Route name
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
        $redirect = $this->urlGenerator->generate($redirect, [], UrlGeneratorInterface::ABSOLUTE_URL);
        return parent::tokenExchange($code, $redirect, $scopes, $grantType);
    }
}