<?php


namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bytes\DiscordBundle\Controller\OAuthController;
use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\DiscordClient;
use Bytes\DiscordBundle\HttpClient\DiscordUserClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Services\OAuth;

/**
 * @param ContainerConfigurator $container
 */
return static function (ContainerConfigurator $container) {

    $services = $container->services();

    $services->set('bytes_discord.oauth', OAuth::class)
        ->args([
            service('security.helper'), // Symfony\Component\Security\Core\Security
            service('router.default'), // Symfony\Component\Routing\Generator\UrlGeneratorInterface
            '', // $config['client_id']
            '', // $config['redirects']
            '', // $config['user']
        ])
        ->alias(OAuth::class, 'bytes_discord.oauth')
        ->public();

    $services->set('bytes_discord.oauth_controller', OAuthController::class)
        ->args([
            service('bytes_discord.oauth'), // Bytes\DiscordBundle\Services\OAuth
        ])
        ->alias(OAuthController::class, 'bytes_discord.oauth_controller')
        ->public();

    $services->set('bytes_discord.httpclient.retry_strategy.discord', DiscordRetryStrategy::class)
        ->alias(DiscordRetryStrategy::class, 'bytes_discord.httpclient.retry_strategy.discord')
        ->public();

    $services->set('bytes_discord.httpclient.discord', DiscordClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            null, // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            service('validator'), // Symfony\Component\Validator\Validator\ValidatorInterface
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['bot_token']
            '', // $config['user_agent']
        ])
        ->alias(DiscordClient::class, 'bytes_discord.httpclient.discord')
        ->public();

    $services->set('bytes_discord.httpclient.discord.bot', DiscordBotClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('bytes_discord.httpclient.retry_strategy.discord'), // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            service('validator'), // Symfony\Component\Validator\Validator\ValidatorInterface
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['bot_token']
            '', // $config['user_agent']
        ])
        ->alias(DiscordBotClient::class, 'bytes_discord.httpclient.discord.bot')
        ->public();

    $services->set('bytes_discord.httpclient.discord.user', DiscordUserClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('bytes_discord.httpclient.retry_strategy.discord'), // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            service('validator'), // Symfony\Component\Validator\Validator\ValidatorInterface
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['user_agent']
        ])
        ->alias(DiscordUserClient::class, 'bytes_discord.httpclient.discord.user')
        ->public();

};