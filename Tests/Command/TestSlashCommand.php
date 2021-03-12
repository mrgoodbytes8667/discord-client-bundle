<?php

namespace Bytes\DiscordBundle\Tests\Command;

use Bytes\DiscordBundle\Tests\TestingKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\String\u;

/**
 * Class TestSlashCommand
 * @package Bytes\DiscordBundle\Tests\Command
 */
abstract class TestSlashCommand extends TestCase
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var TestingKernel|null
     */
    protected $kernel;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @param string $callback
     * @param array $inputs
     * @param array $config = ['client_id' => '', 'client_secret' => '', 'client_public_key' => '', 'bot_token' => '', 'user_agent' => '']
     * @param bool $registerSlashCommands
     *
     * @return CommandTester
     */
    protected function setupApplication(string $callback, array $inputs = [], array $config = [], bool $registerSlashCommands = true)
    {
        $kernel = $this->kernel;
        $kernel->setCallback($callback);
        if (!empty($config)) {
            $kernel->mergeConfig($config);
        }
        $kernel->setRegisterSlashCommands($registerSlashCommands);
        $kernel->boot();
        $container = $kernel->getContainer();

        $application = new Application($kernel);

        $command = $application->find($this->command);
        $commandTester = new CommandTester($command);
        if (!empty($inputs)) {
            $commandTester->setInputs($inputs);
        }

        return $commandTester;
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->kernel = new TestingKernel();
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        if (is_null($this->fs)) {
            $this->fs = new Filesystem();
        }
        $this->fs->remove($this->kernel->getCacheDir());
        $this->kernel = null;
    }
}

