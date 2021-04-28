<?php


namespace Bytes\DiscordBundle\DependencyInjection;

use Bytes\DiscordResponseBundle\Enums\OAuthScopes;
use Bytes\DiscordResponseBundle\Enums\Permissions;
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
            ->ignoreExtraKeys()
            ->children()
                ->scalarNode('client_id')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('The client id')
                    ->defaultValue('')
                ->end()
                ->scalarNode('client_secret')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('The client secret')
                    ->defaultValue('')
                    ->end()
                ->scalarNode('client_public_key')
                    ->info('The client public key')
                    ->defaultValue('')
                ->end()
                ->scalarNode('bot_token')
                    ->info('The bot token')
                    ->defaultValue('')
                ->end()
                ->scalarNode('user_agent')
                    ->info('The user agent string. Format must be [Name] ([URL], [VERSION])')
                    ->defaultNull()
                ->end()
                ->booleanNode('user')
                    ->info('Should security be passed to the child OAuth handler?')
                    ->defaultFalse()
                ->end()
                ->arrayNode('endpoints')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('redirects')
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
                            ->arrayNode('permissions')
                                ->addDefaultsIfNotSet()
                                ->info('String constants from the Permissions enum class')
                                ->children()
                                    ->arrayNode('add')
                                        ->scalarPrototype()
                                            ->beforeNormalization()
                                                ->always()
                                                ->then(function ($v) { return (new Permissions($v))->value; })
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('remove')
                                        ->scalarPrototype()
                                            ->beforeNormalization()
                                                ->always()
                                                ->then(function ($v) { return (new Permissions($v))->value; })
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('scopes')
                                ->addDefaultsIfNotSet()
                                ->info('String constants from the OAuthScopes enum class')
                                ->children()
                                    ->arrayNode('add')
                                        ->scalarPrototype()
                                            ->beforeNormalization()
                                                ->always()
                                                ->then(function ($v) { return (new OAuthScopes($v))->value; })
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('remove')
                                        ->scalarPrototype()
                                            ->beforeNormalization()
                                                ->always()
                                                ->then(function ($v) { return (new OAuthScopes($v))->value; })
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}