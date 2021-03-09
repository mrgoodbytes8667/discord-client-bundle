<?php


namespace Bytes\DiscordBundle;


use Bytes\DiscordBundle\DependencyInjection\Compiler\SlashCommandsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BytesDiscordBundle
 * @package Bytes\DiscordBundle
 */
class BytesDiscordBundle extends Bundle
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