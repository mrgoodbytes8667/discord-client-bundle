<?php

namespace Bytes\DiscordClientBundle\Tests\DependencyInjection\Compiler;

use Bytes\DiscordClientBundle\DependencyInjection\Compiler\SlashCommandsPass;
use Bytes\DiscordClientBundle\Handler\SlashCommandsHandlerCollection;
use Bytes\DiscordClientBundle\Tests\Fixtures\Commands\Bar;
use Bytes\DiscordClientBundle\Tests\Fixtures\Commands\Foo;
use Bytes\DiscordClientBundle\Tests\Fixtures\Commands\Sample;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SlashCommandsPassTest
 * @package Bytes\DiscordClientBundle\Tests\DependencyInjection\Compiler
 */
class SlashCommandsPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     *
     */
    public function testPassRunsSuccessfully()
    {
        $container = $this->container;

        $this->addToAssertionCount(1);
    }

    /**
     * @before
     * @return ContainerBuilder
     * @throws \ReflectionException
     */
    public function setUpContainer()
    {
        $container = new ContainerBuilder();
        $container->register('bytes_discord_client.slashcommands.handler');

        $container->register('bytes_discord_client.sample', Sample::class)->addTag('bytes_discord_client.slashcommand');
        $container->register('bytes_discord_client.foo', Foo::class)->addTag('bytes_discord_client.slashcommand');
        $container->register('bytes_discord_client.bar', Bar::class)->addTag('bytes_discord_client.slashcommand');

        $serializerPass = new SlashCommandsPass();
        $serializerPass->process($container);

        return $this->container = $container;
    }

    /**
     *
     */
    public function testFindingTaggedServices()
    {
        $container = $this->container;

        $this->assertCount(3, $container->findTaggedServiceIds('bytes_discord_client.slashcommand'));
    }

    /**
     * Cannot get actual commands without autowiring
     */
    public function testGetCommandClass()
    {
        $handler = $this->container->get('bytes_discord_client.slashcommands.handler');
        $commandClass = $handler->getCommandClass('sample');
        $this->assertEquals('bytes_discord_client.sample', $commandClass);
    }

    /**
     *
     */
    public function testGetList()
    {
        $handler = $this->container->get('bytes_discord_client.slashcommands.handler');
        $this->assertCount(3, $handler->getList());
    }

    /**
     * Cannot get commands without autowiring
     */
    public function testGetCommands()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot find class with name");
        $handler = $this->container->get('bytes_discord_client.slashcommands.handler');
        $commands = $handler->getCommands();
        $this->assertCount(3, $commands);
    }

    /**
     *
     */
    public function testSetList()
    {
        $handler = $this->container->get('bytes_discord_client.slashcommands.handler');
        $list = [];
        $this->assertInstanceOf(SlashCommandsHandlerCollection::class, $handler->setList($list));
        $this->assertCount(0, $handler->getList());

        $list = [
            'sample' => 'bytes_discord_client.sample'
        ];
        $this->assertInstanceOf(SlashCommandsHandlerCollection::class, $handler->setList($list));
        $this->assertCount(1, $handler->getList());
    }

    /**
     * Cannot get commands without autowiring
     */
    public function testGetCommand()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot find class with name");
        $handler = $this->container->get('bytes_discord_client.slashcommands.handler');
        $handler->getCommand('sample');
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        $this->container = null;
    }
}
