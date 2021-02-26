<?php


namespace Bytes\DiscordBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Class BytesDiscordExtension
 * @package Bytes\DiscordBundle\DependencyInjection
 */
class BytesDiscordExtension extends Extension implements ExtensionInterface
{
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

        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('bytes_discord.oauth');
        $definition->replaceArgument(1, $config['client_id']);
        $definition->replaceArgument(2, $config['redirects']['user_route_name']);
        $definition->replaceArgument(3, $config['redirects']['bot_route_name']);
        $definition->replaceArgument(4, $config['redirects']['login_route_name']);
        $definition->replaceArgument(5, $config['redirects']['slash_route_name']);

        $definition = $container->getDefinition('bytes_discord.oauth_controller');
        $definition->replaceArgument(2, $config['user']);
    }
}