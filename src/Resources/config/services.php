<?php


namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bytes\DiscordBundle\Command\SlashAddCommand;
use Bytes\DiscordBundle\Command\SlashDeleteCommand;
use Bytes\DiscordBundle\Controller\CommandController;
use Bytes\DiscordBundle\Controller\OAuthController;
use Bytes\DiscordBundle\Handler\SlashCommandsHandlerCollection;
use Bytes\DiscordBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordBundle\HttpClient\Api\DiscordClient;
use Bytes\DiscordBundle\HttpClient\Api\DiscordUserClient;
use Bytes\DiscordBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordBundle\HttpClient\Token\DiscordBotTokenClient;
use Bytes\DiscordBundle\HttpClient\Token\DiscordUserTokenClient;
use Bytes\DiscordBundle\HttpClient\Token\DiscordUserTokenResponse;
use Bytes\DiscordBundle\Request\DiscordConverter;
use Bytes\DiscordBundle\Request\DiscordGuildConverter;
use Bytes\DiscordBundle\Routing\DiscordBotOAuth;
use Bytes\DiscordBundle\Routing\DiscordLoginOAuth;
use Bytes\DiscordBundle\Routing\DiscordUserOAuth;
use Bytes\ResponseBundle\HttpClient\Token\AppTokenClientInterface;
use Bytes\ResponseBundle\HttpClient\Token\TokenClientInterface;
use Bytes\ResponseBundle\HttpClient\Token\UserTokenClientInterface;
use Bytes\ResponseBundle\Routing\OAuthInterface;
use function Symfony\Component\String\u;

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
        ->call('setResponse', [service('bytes_response.httpclient.response')])
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
        ->call('setResponse', [service('bytes_response.httpclient.response')])
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
        ->call('setResponse', [service('bytes_response.httpclient.response')])
        ->alias(DiscordUserClient::class, 'bytes_discord.httpclient.discord.user')
        ->public();
    //endregion

    //region Clients (Tokens)
    $services->set('bytes_discord.httpclient.discord.token.bot', DiscordBotTokenClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['bot_token']
            '', // $config['user_agent']
        ])
        ->call('setSerializer', [service('serializer')])
        ->call('setValidator', [service('validator')])
        ->call('setDispatcher', [service('event_dispatcher')])
        ->call('setResponse', [service('bytes_discord.httpclient.response.token.user')])
        ->call('setUrlGenerator', [service('router.default')]) // Symfony\Component\Routing\Generator\UrlGeneratorInterface
        ->call('setOAuth', [service('bytes_discord.oauth.bot')])
        ->lazy()
        ->alias(DiscordBotTokenClient::class, 'bytes_discord.httpclient.discord.token.bot')
        ->public();

    $services->alias(TokenClientInterface::class.' $discordBotTokenClient', DiscordBotTokenClient::class);
    $services->alias(AppTokenClientInterface::class.' $discordTokenClient', DiscordBotTokenClient::class);
    $services->alias(AppTokenClientInterface::class.' $discordBotTokenClient', DiscordBotTokenClient::class);
    
    $services->set('bytes_discord.httpclient.discord.token.user', DiscordUserTokenClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['user_agent']
        ])
        ->call('setSerializer', [service('serializer')])
        ->call('setValidator', [service('validator')])
        ->call('setDispatcher', [service('event_dispatcher')])
        ->call('setResponse', [service('bytes_discord.httpclient.response.token.user')])
        ->call('setUrlGenerator', [service('router.default')]) // Symfony\Component\Routing\Generator\UrlGeneratorInterface
        ->call('setOAuth', [service('bytes_discord.oauth.user')])
        ->lazy()
        ->alias(DiscordUserTokenClient::class, 'bytes_discord.httpclient.discord.token.user')
        ->public();

    $services->alias(TokenClientInterface::class.' $discordUserTokenClient', DiscordUserTokenClient::class);
    $services->alias(UserTokenClientInterface::class.' $discordTokenClient', DiscordUserTokenClient::class);
    $services->alias(UserTokenClientInterface::class.' $discordUserTokenClient', DiscordUserTokenClient::class);
    //endregion

    //region Response
    $services->set('bytes_discord.httpclient.response.token.user', DiscordUserTokenResponse::class)
        ->args([
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
            service('event_dispatcher'), // Symfony\Component\Serializer\SerializerInterface
        ])
        ->alias(DiscordUserTokenResponse::class, 'bytes_discord.httpclient.response.token.user')
        ->public();
    //endregion

    //region HttpClient Retry Strategies
    $services->set('bytes_discord.httpclient.retry_strategy.discord', DiscordRetryStrategy::class)
        ->alias(DiscordRetryStrategy::class, 'bytes_discord.httpclient.retry_strategy.discord')
        ->public();
    //endregion

    //region Routing
    foreach(['bot' => DiscordBotOAuth::class, 'login' => DiscordLoginOAuth::class, 'user' => DiscordUserOAuth::class] as $tag => $class) {
        $services->set('bytes_discord.oauth.' . $tag, $class)
            ->args([
                '', // $config['client_id']
                [],
                [] // $config['options']
            ])
            ->call('setUrlGenerator', [service('router.default')]) // Symfony\Component\Routing\Generator\UrlGeneratorInterface
            ->call('setValidator', [service('validator')])
            ->call('setSecurity', [service('security.helper')->ignoreOnInvalid()]) // Symfony\Component\Security\Core\Security
            ->lazy()
            ->alias($class, 'bytes_discord.oauth.' . $tag)
            ->public();

        $alias = u($tag)->title()->prepend(OAuthInterface::class . ' $discord')->append('OAuth')->toString();

        $services->alias($alias, $class);
    }
    //endregion

    //region Controllers
    $services->set('bytes_discord.oauth_controller', OAuthController::class)
        ->args([
            service('bytes_discord.oauth.bot'),
            service('bytes_discord.oauth.login'),
            service('bytes_discord.oauth.user'),
        ])
        ->alias(OAuthController::class, 'bytes_discord.oauth_controller')
        ->public();

    $services->set('bytes_discord.command_controller', CommandController::class)
        ->args([
            service('bytes_discord.httpclient.discord.bot'), // Bytes\DiscordBundle\HttpClient\Api\DiscordBotClient
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
            service('bytes_discord.httpclient.discord.bot'), // Bytes\DiscordBundle\HttpClient\Api\DiscordBotClient
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
            service('bytes_discord.slashcommands.handler'), // Bytes\DiscordBundle\Handler\SlashCommandsHandlerCollection
        ])
        ->tag('console.command', ['command' => 'bytes_discord:slash:add']);

    $services->set(null, SlashDeleteCommand::class)
        ->args([
            service('bytes_discord.httpclient.discord.bot'), // Bytes\DiscordBundle\HttpClient\Api\DiscordBotClient
        ])
        ->tag('console.command', ['command' => 'bytes_discord:slash:delete']);
    //endregion

    //region Converters
    $services->set('bytes_discord.discord_guild_converter', DiscordGuildConverter::class)
        ->args([
            service('bytes_discord.httpclient.discord.bot'), // Bytes\DiscordBundle\HttpClient\Api\DiscordBotClient
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