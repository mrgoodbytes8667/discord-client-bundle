<?php


namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bytes\DiscordClientBundle\Command\SlashAddCommand;
use Bytes\DiscordClientBundle\Command\SlashDeleteCommand;
use Bytes\DiscordClientBundle\Command\SlashPermissionsCommand;
use Bytes\DiscordClientBundle\Controller\CommandController;
use Bytes\DiscordClientBundle\EventListener\RevokeTokenSubscriber;
use Bytes\DiscordClientBundle\Handler\SlashCommandsHandlerCollection;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordClient;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordUserClient;
use Bytes\DiscordClientBundle\HttpClient\Response\DiscordResponse;
use Bytes\DiscordClientBundle\HttpClient\Retry\DiscordRetryStrategy;
use Bytes\DiscordClientBundle\HttpClient\Token\DiscordBotTokenClient;
use Bytes\DiscordClientBundle\HttpClient\Token\DiscordUserTokenClient;
use Bytes\DiscordClientBundle\HttpClient\Token\DiscordUserTokenResponse;
use Bytes\DiscordClientBundle\Routing\DiscordBotOAuth;
use Bytes\DiscordClientBundle\Routing\DiscordLoginOAuth;
use Bytes\DiscordClientBundle\Routing\DiscordUserOAuth;
use Bytes\ResponseBundle\Controller\OAuthController;
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
    $services->set('bytes_discord_client.httpclient.discord', DiscordClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('event_dispatcher'),
            null, // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['bot_token']
            '', // $config['user_agent']
        ])
        ->call('setResponse', [service('bytes_discord_client.httpclient.response')])
        ->tag('bytes_response.http_client')
        ->tag('bytes_response.http_client.api')
        ->lazy()
        ->alias(DiscordClient::class, 'bytes_discord_client.httpclient.discord')
        ->public();

    $services->set('bytes_discord_client.httpclient.discord.bot', DiscordBotClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('event_dispatcher'),
            service('bytes_discord_client.httpclient.retry_strategy.discord'), // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['bot_token']
            '', // $config['user_agent']
        ])
        ->call('setResponse', [service('bytes_discord_client.httpclient.response')])
        ->tag('bytes_response.http_client')
        ->tag('bytes_response.http_client.api')
        ->alias(DiscordBotClient::class, 'bytes_discord_client.httpclient.discord.bot')
        ->public();

    $services->set('bytes_discord_client.httpclient.discord.user', DiscordUserClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('event_dispatcher'),
            service('bytes_discord_client.httpclient.retry_strategy.discord'), // Symfony\Component\HttpClient\Retry\RetryStrategyInterface
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['user_agent']
        ])
        ->call('setResponse', [service('bytes_discord_client.httpclient.response')])
        ->tag('bytes_response.http_client')
        ->tag('bytes_response.http_client.api')
        ->alias(DiscordUserClient::class, 'bytes_discord_client.httpclient.discord.user')
        ->public();
    //endregion

    //region Clients (Tokens)
    $services->set('bytes_discord_client.httpclient.discord.token.bot', DiscordBotTokenClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('event_dispatcher'),
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['bot_token']
            '', // $config['user_agent']
            '', // revoke_on_refresh
            '', // fire_revoke_on_refresh
        ])
        ->call('setResponse', [service('bytes_discord_client.httpclient.response.token.user')])
        ->call('setOAuth', [service('bytes_discord_client.oauth.bot')])
        ->tag('bytes_response.http_client')
        ->tag('bytes_response.http_client.token')
        ->lazy()
        ->alias(DiscordBotTokenClient::class, 'bytes_discord_client.httpclient.discord.token.bot')
        ->public();

    $services->alias(TokenClientInterface::class.' $discordBotTokenClient', DiscordBotTokenClient::class);
    $services->alias(AppTokenClientInterface::class.' $discordTokenClient', DiscordBotTokenClient::class);
    $services->alias(AppTokenClientInterface::class.' $discordBotTokenClient', DiscordBotTokenClient::class);

    $services->set('bytes_discord_client.httpclient.discord.token.user', DiscordUserTokenClient::class)
        ->args([
            service('http_client'), // Symfony\Contracts\HttpClient\HttpClientInterface
            service('event_dispatcher'),
            '', // $config['client_id']
            '', // $config['client_secret']
            '', // $config['user_agent']
            '', // revoke_on_refresh
            '', // fire_revoke_on_refresh
        ])
        ->call('setResponse', [service('bytes_discord_client.httpclient.response.token.user')])
        ->call('setOAuth', [service('bytes_discord_client.oauth.user')])
        ->tag('bytes_response.http_client')
        ->tag('bytes_response.http_client.token')
        ->lazy()
        ->alias(DiscordUserTokenClient::class, 'bytes_discord_client.httpclient.discord.token.user')
        ->public();

    $services->alias(TokenClientInterface::class.' $discordUserTokenClient', DiscordUserTokenClient::class);
    $services->alias(UserTokenClientInterface::class.' $discordTokenClient', DiscordUserTokenClient::class);
    $services->alias(UserTokenClientInterface::class.' $discordUserTokenClient', DiscordUserTokenClient::class);
    //endregion

    //region Response
    $services->set('bytes_discord_client.httpclient.response', DiscordResponse::class)
        ->args([
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
            service('event_dispatcher'), // Symfony\Component\Serializer\SerializerInterface
        ])
        ->alias(DiscordResponse::class, 'bytes_discord_client.httpclient.response')
        ->public();

    $services->set('bytes_discord_client.httpclient.response.token.user', DiscordUserTokenResponse::class)
        ->args([
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
            service('event_dispatcher'), // Symfony\Component\Serializer\SerializerInterface
        ])
        ->alias(DiscordUserTokenResponse::class, 'bytes_discord_client.httpclient.response.token.user')
        ->public();
    //endregion

    //region HttpClient Retry Strategies
    $services->set('bytes_discord_client.httpclient.retry_strategy.discord', DiscordRetryStrategy::class)
        ->alias(DiscordRetryStrategy::class, 'bytes_discord_client.httpclient.retry_strategy.discord')
        ->public();
    //endregion

    //region Routing
    foreach(['bot' => DiscordBotOAuth::class, 'login' => DiscordLoginOAuth::class, 'user' => DiscordUserOAuth::class] as $tag => $class) {
        $services->set('bytes_discord_client.oauth.' . $tag, $class)
            ->args([
                '', // $config['client_id']
                [],
                [] // $config['options']
            ])
            ->call('setUrlGenerator', [service('router.default')]) // Symfony\Component\Routing\Generator\UrlGeneratorInterface
            ->call('setValidator', [service('validator')])
            ->call('setSecurity', [service('security.helper')->ignoreOnInvalid()]) // Symfony\Component\Security\Core\Security
            ->tag('bytes_response.oauth')
            ->lazy()
            ->alias($class, 'bytes_discord_client.oauth.' . $tag)
            ->public();

        $alias = u($tag)->title()->prepend(OAuthInterface::class . ' $discord')->append('OAuth')->toString();

        $services->alias($alias, $class);
    }
    
    //endregion

    //region Controllers
    foreach (['bot', 'login', 'user'] as $type) {
        $services->set(sprintf('bytes_discord_client.oauth_controller.%s', $type), OAuthController::class)
            ->args([
                service(sprintf('bytes_discord_client.oauth.%s', $type)), // Bytes\ResponseBundle\Routing\OAuthInterface
                service('router.default'), // Symfony\Component\Routing\Generator\UrlGeneratorInterface
                '', // destination route
            ])
            ->public();
    }

    $services->set('bytes_discord_client.command_controller', CommandController::class)
        ->args([
            service('bytes_discord_client.httpclient.discord.bot'), // Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient
            service('serializer'), // Symfony\Component\Serializer\SerializerInterface
        ])
        ->alias(CommandController::class, 'bytes_discord_client.command_controller')
        ->public();
    //endregion

    //region Handlers
    $services->set('bytes_discord_client.slashcommands.handler', SlashCommandsHandlerCollection::class)
        ->args([tagged_locator('bytes_discord_client.slashcommand', 'key', 'getDefaultIndexName')])
        ->alias(SlashCommandsHandlerCollection::class, 'bytes_discord_client.slashcommands.handler')
        ->public();
    //endregion

    //region Commands
    $services->set(null, SlashAddCommand::class)
        ->args([
            service('bytes_discord_client.httpclient.discord.bot'), // Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient
            service('bytes_discord_client.slashcommands.handler'), // Bytes\DiscordClientBundle\Handler\SlashCommandsHandlerCollection
        ])
        ->call('setEntityManager', [service('doctrine.orm.default_entity_manager')->ignoreOnInvalid()]) // Doctrine\ORM\EntityManagerInterface
        ->tag('console.command', ['command' => 'bytes_discord_client:slash:add']);

    $services->set(null, SlashDeleteCommand::class)
        ->args([
            service('bytes_discord_client.httpclient.discord.bot'), // Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient
        ])
        ->call('setEntityManager', [service('doctrine.orm.default_entity_manager')->ignoreOnInvalid()]) // Doctrine\ORM\EntityManagerInterface
        ->tag('console.command', ['command' => 'bytes_discord_client:slash:delete']);

    $services->set(null, SlashPermissionsCommand::class)
        ->args([
            service('bytes_discord_client.httpclient.discord.bot'), // Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient
            service('bytes_discord_client.slashcommands.handler'), // Bytes\DiscordClientBundle\Handler\SlashCommandsHandlerCollection
        ])
        ->call('setEntityManager', [service('doctrine.orm.default_entity_manager')->ignoreOnInvalid()]) // Doctrine\ORM\EntityManagerInterface
        ->tag('console.command', ['command' => 'bytes_discord_client:slash:permissions']);
    //endregion

    //region Subscribers
    $services->set('bytes_discord_client.subscriber.revoke_token', RevokeTokenSubscriber::class)
        ->args([
            service('bytes_discord_client.httpclient.discord.token.user'),
        ])
        ->tag('kernel.event_subscriber');
    //endregion
};