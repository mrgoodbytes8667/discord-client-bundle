<?php


namespace Bytes\DiscordClientBundle\DependencyInjection;


use Bytes\DiscordClientBundle\Slash\SlashCommandInterface;
use Bytes\ResponseBundle\DependencyInjection\ResponseExtensionInterface;
use Bytes\ResponseBundle\Objects\ConfigNormalizer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Class BytesDiscordClientExtension
 * @package Bytes\DiscordClientBundle\DependencyInjection
 */
class BytesDiscordClientExtension extends Extension implements ExtensionInterface, ResponseExtensionInterface
{
    /**
     * @var string[]
     */
    public static $endpoints = ['bot', 'login', 'user'];

    /**
     * @var string[]
     */
    public static $addRemoveParents = ['permissions', 'scopes'];

    /**
     * @return string[]
     */
    public static function getEndpoints(): array
    {
        return self::$endpoints;
    }

    /**
     * @return string[]
     */
    public static function getAddRemoveParents(): array
    {
        return self::$addRemoveParents;
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);

        /** @var array $config = ['client_id' => '', 'client_secret' => '', 'bot_token' => '', 'user' => false, 'redirects' => ['bot' => ['method' => '', 'route_name' => '', 'url' => '']], 'user' => ['method' => '', 'route_name' => '', 'url' => '']], 'slash' => ['method' => '', 'route_name' => '', 'url' => '']], 'login' => ['method' => '', 'route_name' => '', 'url' => '']]]*/
        $config = $this->processConfiguration($configuration, $configs);

        $config = ConfigNormalizer::normalizeEndpoints($config, static::$endpoints, static::$addRemoveParents, fireRevokeOnRefresh: true);

        $container->registerForAutoconfiguration(SlashCommandInterface::class)
            ->addTag('bytes_discord_client.slashcommand');

        $definition = $container->getDefinition('bytes_discord_client.httpclient.discord');
        $definition->replaceArgument(3, $config['client_id']);
        $definition->replaceArgument(4, $config['client_secret']);
        $definition->replaceArgument(5, $config['bot_token']);
        $definition->replaceArgument(6, $config['user_agent']);

        $definition = $container->getDefinition('bytes_discord_client.httpclient.discord.bot');
        $definition->replaceArgument(3, $config['client_id']);
        $definition->replaceArgument(4, $config['client_secret']);
        $definition->replaceArgument(5, $config['bot_token']);
        $definition->replaceArgument(6, $config['user_agent']);

        $definition = $container->getDefinition('bytes_discord_client.httpclient.discord.user');
        $definition->replaceArgument(3, $config['client_id']);
        $definition->replaceArgument(4, $config['client_secret']);
        $definition->replaceArgument(5, $config['user_agent']);

        $definition = $container->getDefinition('bytes_discord_client.httpclient.discord.token.bot');
        $definition->replaceArgument(2, $config['client_id']);
        $definition->replaceArgument(3, $config['client_secret']);
        $definition->replaceArgument(4, $config['bot_token']);
        $definition->replaceArgument(5, $config['user_agent']);
        $definition->replaceArgument(6, $config['endpoints']['bot']['revoke_on_refresh']);
        $definition->replaceArgument(7, $config['endpoints']['bot']['fire_revoke_on_refresh']);

        $definition = $container->getDefinition('bytes_discord_client.httpclient.discord.token.user');
        $definition->replaceArgument(2, $config['client_id']);
        $definition->replaceArgument(3, $config['client_secret']);
        $definition->replaceArgument(4, $config['user_agent']);
        $definition->replaceArgument(5, $config['endpoints']['user']['revoke_on_refresh']);
        $definition->replaceArgument(6, $config['endpoints']['user']['fire_revoke_on_refresh']);

        foreach (['bytes_discord_client.oauth.bot', 'bytes_discord_client.oauth.login', 'bytes_discord_client.oauth.user'] as $value) {
            $definition = $container->getDefinition($value);
            $definition->replaceArgument(0, $config['client_id']);
            $definition->replaceArgument(1, $config['endpoints']);
        }

        foreach (['bot', 'login', 'user'] as $type) {
            $container->getDefinition(sprintf('bytes_discord_client.oauth_controller.%s', $type))
                ->replaceArgument(2, $config['login_success_route']);
        }
    }
}