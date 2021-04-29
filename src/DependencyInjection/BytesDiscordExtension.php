<?php


namespace Bytes\DiscordBundle\DependencyInjection;


use Bytes\DiscordBundle\Slash\SlashCommandInterface;
use Bytes\ResponseBundle\DependencyInjection\ResponseExtensionInterface;
use Bytes\ResponseBundle\Objects\ConfigNormalizer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Class BytesDiscordExtension
 * @package Bytes\DiscordBundle\DependencyInjection
 */
class BytesDiscordExtension extends Extension implements ExtensionInterface, ResponseExtensionInterface
{
    /**
     * @var string[]
     */
    public static $endpoints = ['bot', 'login', 'slash', 'user'];

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

        /** @var array $config = ['client_id' => '', 'client_secret' => '', 'client_public_key' => '', 'bot_token' => '', 'user' => false, 'redirects' => ['bot' => ['method' => '', 'route_name' => '', 'url' => '']], 'user' => ['method' => '', 'route_name' => '', 'url' => '']], 'slash' => ['method' => '', 'route_name' => '', 'url' => '']], 'login' => ['method' => '', 'route_name' => '', 'url' => '']]]*/
        $config = $this->processConfiguration($configuration, $configs);

        $config = ConfigNormalizer::normalizeEndpoints($config, static::$endpoints, static::$addRemoveParents);

        $container->registerForAutoconfiguration(SlashCommandInterface::class)
            ->addTag('bytes_discord.slashcommand');

        $definition = $container->getDefinition('bytes_discord.oauth');
        $definition->replaceArgument(2, $config['client_id']);
        $definition->replaceArgument(3, $config['endpoints']);
        $definition->replaceArgument(4, $config['user']);

        $definition = $container->getDefinition('bytes_discord.httpclient.discord');
        $definition->replaceArgument(2, $config['client_id']);
        $definition->replaceArgument(3, $config['client_secret']);
        $definition->replaceArgument(4, $config['bot_token']);
        $definition->replaceArgument(5, $config['user_agent']);

        $definition = $container->getDefinition('bytes_discord.httpclient.discord.bot');
        $definition->replaceArgument(2, $config['client_id']);
        $definition->replaceArgument(3, $config['client_secret']);
        $definition->replaceArgument(4, $config['bot_token']);
        $definition->replaceArgument(5, $config['user_agent']);

        $definition = $container->getDefinition('bytes_discord.httpclient.discord.user');
        $definition->replaceArgument(2, $config['client_id']);
        $definition->replaceArgument(3, $config['client_secret']);
        $definition->replaceArgument(4, $config['user_agent']);

        $definition = $container->getDefinition('bytes_discord.httpclient.discord.token');
        $definition->replaceArgument(1, $config['client_id']);
        $definition->replaceArgument(2, $config['client_secret']);
        $definition->replaceArgument(3, $config['user_agent']);
    }
}