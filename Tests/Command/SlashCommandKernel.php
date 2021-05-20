<?php

namespace Bytes\DiscordClientBundle\Tests\Command;

use Bytes\DiscordClientBundle\Tests\Fixtures\Commands\Bar;
use Bytes\DiscordClientBundle\Tests\Fixtures\Commands\Foo;
use Bytes\DiscordClientBundle\Tests\Fixtures\Commands\Sample;
use Bytes\DiscordClientBundle\Tests\Kernel;
use Exception;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SlashCommandKernel
 * @package Bytes\DiscordClientBundle\Tests
 */
class SlashCommandKernel extends Kernel
{
    /**
     * @var bool
     */
    private $registerSlashCommands = true;

    /**
     * @param bool $registerSlashCommands
     * @return $this
     */
    public function setRegisterSlashCommands(bool $registerSlashCommands): self
    {
        $this->registerSlashCommands = $registerSlashCommands;
        return $this;
    }

    /**
     * @param LoaderInterface $loader
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(function (ContainerBuilder $container) {
            if ($this->registerSlashCommands) {
                $container->register(Sample::class)->addTag('bytes_discord_client.slashcommand');
                $container->register(Foo::class)->addTag('bytes_discord_client.slashcommand');
                $container->register(Bar::class)->addTag('bytes_discord_client.slashcommand');
            }
        });
    }
}
