<?php


namespace Bytes\DiscordClientBundle;


use Bytes\DiscordClientBundle\DependencyInjection\Compiler\SlashCommandsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BytesDiscordClientBundle
 * @package Bytes\DiscordClientBundle
 */
class BytesDiscordClientBundle extends Bundle
{
    /**
     * Use this method to register compiler passes and manipulate the container during the building process.
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SlashCommandsPass());
    }
}