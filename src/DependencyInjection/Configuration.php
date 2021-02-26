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
                ->booleanNode('user')->defaultFalse()->end()
                ->arrayNode('redirects')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('bot')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->enumNode('method')
                                    ->values(['route_name', 'url'])
                                    ->defaultValue('route_name')
                                ->end()
                                ->scalarNode('route_name')->defaultValue('')->end()
                                ->scalarNode('url')->defaultValue('')->end()
                            ->end()
                        ->end()
                        ->arrayNode('slash')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->enumNode('method')
                                    ->values(['route_name', 'url'])
                                    ->defaultValue('route_name')
                                ->end()
                                ->scalarNode('route_name')->defaultValue('')->end()
                                ->scalarNode('url')->defaultValue('')->end()
                            ->end()
                        ->end()
                        ->arrayNode('user')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->enumNode('method')
                                    ->values(['route_name', 'url'])
                                    ->defaultValue('route_name')
                                ->end()
                                ->scalarNode('route_name')->defaultValue('')->end()
                                ->scalarNode('url')->defaultValue('')->end()
                            ->end()
                        ->end()
                        ->arrayNode('login')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->enumNode('method')
                                    ->values(['route_name', 'url'])
                                    ->defaultValue('route_name')
                                ->end()
                                ->scalarNode('route_name')->defaultValue('')->end()
                                ->scalarNode('url')->defaultValue('')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}