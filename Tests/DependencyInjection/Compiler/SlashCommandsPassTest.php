<?php

namespace Bytes\DiscordBundle\Tests\DependencyInjection\Compiler;

use Bytes\DiscordBundle\DependencyInjection\Compiler\SlashCommandsPass;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Bar;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Foo;
use Bytes\DiscordBundle\Tests\Fixtures\Commands\Sample;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SlashCommandsPassTest
 * @package Bytes\DiscordBundle\Tests\DependencyInjection\Compiler
 */
class SlashCommandsPassTest extends TestCase
{

    /**
     * @group dependency-injection
     */
    public function testPassRunsSuccessfully()
    {
        $container = new ContainerBuilder();
        $container->register('bytes_discord.slashcommands.handler');

        $serializerPass = new SlashCommandsPass();
        $serializerPass->process($container);

        $this->addToAssertionCount(1);
    }

    /**
     * @group dependency-injection
     */
    public function testFindingTaggedServices()
    {
        $container = new ContainerBuilder();
        $container->register('bytes_discord.slashcommands.handler');

        $container->register('bytes_discord.sample', Sample::class)->addTag('bytes_discord.slashcommand');
        $container->register('bytes_discord.foo', Foo::class)->addTag('bytes_discord.slashcommand');
        $container->register('bytes_discord.bar', Bar::class)->addTag('bytes_discord.slashcommand');

        $serializerPass = new SlashCommandsPass();
        $serializerPass->process($container);

        $this->assertCount(3, $container->findTaggedServiceIds('bytes_discord.slashcommand'));

        $handler = $container->get('bytes_discord.slashcommands.handler');
        $this->assertCount(3, $handler->getList());
    }
}
