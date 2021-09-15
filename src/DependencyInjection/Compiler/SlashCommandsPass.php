<?php


namespace Bytes\DiscordClientBundle\DependencyInjection\Compiler;


use Bytes\DiscordClientBundle\Handler\SlashCommandsHandlerCollection;
use Bytes\DiscordClientBundle\Slash\SlashCommandInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class SlashCommandsPass
 * @package Bytes\DiscordClientBundle\DependencyInjection\Compiler
 */
class SlashCommandsPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     * @param ContainerBuilder $container
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
//        $commands = $container->findTaggedServiceIds('bytes_discord_client.slashcommand');
//
//        $serializerDefinition = $container->getDefinition(SlashCommandsHandlerCollection::class);
//        $serializerDefinition->replaceArgument(0, array_keys($commands));

        $commandServices = $container->findTaggedServiceIds('bytes_discord_client.slashcommand', true);
        $lazyCommandMap = [];

        foreach ($commandServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $container->getParameterBag()->resolveValue($definition->getClass());

            if (isset($tags[0]['key'])) {
                $commandName = $tags[0]['key'];
            } else {
                if (!$r = $container->getReflectionClass($class)) {
                    throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
                }
                if (!$r->isSubclassOf(SlashCommandInterface::class)) {
                    throw new InvalidArgumentException(sprintf('The service "%s" tagged "%s" must implement "%s".', $id, 'bytes_discord_client.slashcommand', SlashCommandInterface::class));
                }
                $commandName = $class::getDefaultIndexName();
                $parsedCommandName = $class::createCommand()->getName();
                if($commandName !== $parsedCommandName)
                {
                    throw new InvalidArgumentException(sprintf('The command name "%s" does not match the "getDefaultIndexName()" value of "%s".', $parsedCommandName, $commandName));
                }
            }

            unset($tags[0]);
            $lazyCommandMap[$commandName] = $id;

            foreach ($tags as $tag) {
                if (isset($tag['key'])) {
                    $lazyCommandMap[$tag['key']] = $id;
                }
            }
        }

        $container->register('bytes_discord_client.slashcommands.handler', SlashCommandsHandlerCollection::class)
            ->setArguments([$lazyCommandMap]);
    }
}