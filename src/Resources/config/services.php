<?php


namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bytes\DiscordBundle\Command\SlashAddCommand;
use Bytes\DiscordBundle\Command\SlashDeleteCommand;
use Bytes\DiscordBundle\Controller\CommandController;
use Bytes\DiscordBundle\Controller\OAuthController;
use Bytes\DiscordBundle\Handler\SlashCommandsHandlerCollection;
use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\DiscordClient;
use Bytes\DiscordBundle\HttpClient\DiscordResponse;
use Bytes\DiscordBundle\HttpClient\DiscordTokenClient;
use Bytes\DiscordBundle\HttpClient\DiscordUserClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\Request\DiscordConverter;
use Bytes\DiscordBundle\Request\DiscordGuildConverter;
use Bytes\DiscordBundle\Services\Client\DiscordBot;
use Bytes\DiscordBundle\Services\OAuth;

/**
 * @param ContainerConfigurator $container
 */
return static function (ContainerConfigurator $container) {

    $services = $container->services();

    //region Clients
    $services->set('bytes_discord.httpclient.discord', DiscordClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            null, // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['bot_token']
            '', // $config['user_agent']
        ])
        ->call('setSerializer', [service('serializer')])
        ->call('setValidator', [service('validator')])
        ->call('setResponse', [service('bytes_discord.httpclient.discord.response')])
        ->lazy()
        ->alias(DiscordClient::class, 'bytes_discord.httpclient.discord')
        ->public();

    $services->set('bytes_discord.httpclient.discord.bot', DiscordBotClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('bytes_discord.httpclient.retry_strategy.discord'), // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['bot_token']
            '', // $config['user_agent']
        ])
        ->call('setSerializer', [service('serializer')])
        ->call('setValidator', [service('validator')])
        ->call('setResponse', [service('bytes_discord.httpclient.discord.response')])
        ->alias(DiscordBotClient::class, 'bytes_discord.httpclient.discord.bot')
        ->public();

    $services->set('bytes_discord.httpclient.discord.user', DiscordUserClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('bytes_discord.httpclient.retry_strategy.discord'), // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['user_agent']
        ])
        ->call('setSerializer', [service('serializer')])
        ->call('setValidator', [service('validator')])
        ->call('setResponse', [service('bytes_discord.httpclient.discord.response')])
        ->alias(DiscordUserClient::class, 'bytes_discord.httpclient.discord.user')
        ->public();

    $services->set('bytes_discord.httpclient.discord.token', DiscordTokenClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            null, // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            service('router.default'), // Symfony\Component\Routing\Generator\UrlGeneratorInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['user_agent']
        ])
        ->call('setSerializer', [service('serializer')])
        ->call('setValidator', [service('validator')])
        ->call('setResponse', [service('bytes_discord.httpclient.discord.response')])
        ->lazy()
        ->alias(DiscordTokenClient::class, 'bytes_discord.httpclient.discord.token')
        ->public();
    //endregion

    //region Response
    $services->set('bytes_discord.httpclient.discord.response', DiscordResponse::class)
        ->args([
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
        ])
        ->alias(DiscordResponse::class, 'bytes_discord.httpclient.discord.response')
        ->public();
    //endregion

    //region HttpClient Retry Strategies
    $services->set('bytes_discord.httpclient.retry_strategy.discord', DiscordRetryStrategy::class)
        ->alias(DiscordRetryStrategy::class, 'bytes_discord.httpclient.retry_strategy.discord')
        ->public();
    //endregion

    //region Services
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
    //endregion

    //region Controllers
    $services->set('bytes_discord.oauth_controller', OAuthController::class)
        ->args([
            service('bytes_discord.oauth'), // Bytes\DiscordBundle\Services\OAuth
        ])
        ->alias(OAuthController::class, 'bytes_discord.oauth_controller')
        ->public();

    $services->set('bytes_discord.command_controller', CommandController::class)
        ->args([
            service('bytes_discord.httpclient.discord.bot'), // Bytes\DiscordBundle\HttpClient\DiscordBotClient
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
        ])
        ->alias(CommandController::class, 'bytes_discord.command_controller')
        ->public();
    //endregion

    //region Handlers
    $services->set('bytes_discord.slashcommands.handler', SlashCommandsHandlerCollection::class)
        ->args([tagged_locator('bytes_discord.slashcommand', 'key', 'getDefaultIndexName')])
        ->alias(SlashCommandsHandlerCollection::class, 'bytes_discord.slashcommands.handler')
        ->public();
    //endregion

    //region Commands
    $services->set(null, SlashAddCommand::class)
        ->args([
            service('bytes_discord.httpclient.discord.bot'), // Bytes\DiscordBundle\HttpClient\DiscordBotClient
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
            service('bytes_discord.slashcommands.handler'), // Bytes\DiscordBundle\Handler\SlashCommandsHandlerCollection
        ])
        ->tag('console.command', ['command' => 'bytes_discord:slash:add']);

    $services->set(null, SlashDeleteCommand::class)
        ->args([
            service('bytes_discord.httpclient.discord.bot'), // Bytes\DiscordBundle\HttpClient\DiscordBotClient
        ])
        ->tag('console.command', ['command' => 'bytes_discord:slash:delete']);
    //endregion

    //region Converters
    $services->set('bytes_discord.discord_guild_converter', DiscordGuildConverter::class)
        ->args([
            service('bytes_discord.httpclient.discord.bot'), // Bytes\DiscordBundle\HttpClient\DiscordBotClient
        ])
        ->tag('request.param_converter', [
            'converter' => 'bytes_discord_guild',
            'priority' => false,
        ]);

    $services->set('bytes_discord.discord_converter', DiscordConverter::class)
        ->tag('request.param_converter', [
            'converter' => 'bytes_discord'
        ]);
    //endregion
};