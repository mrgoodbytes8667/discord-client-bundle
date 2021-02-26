<?php


namespace Bytes\DiscordBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('bytes_discord');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('client_id')->defaultValue('')->end()
                ->scalarNode('client_secret')->defaultValue('')->end()
                ->scalarNode('client_public_key')->defaultValue('')->end()
                ->scalarNode('bot_token')->defaultValue('')->end()
                ->arrayNode('redirects')
                    ->children()
                        ->scalarNode('bot_route_name')->defaultValue('')->end()
                        ->scalarNode('user_route_name')->defaultValue('')->end()
                        ->scalarNode('login_route_name')->defaultValue('')->end()
                        ->scalarNode('slash_route_name')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}