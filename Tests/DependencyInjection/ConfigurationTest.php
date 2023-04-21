<?php

namespace Bytes\DiscordClientBundle\Tests\DependencyInjection;

use Bytes\DiscordClientBundle\Tests\Command\SlashAddCommand\MockSuccessfulAddCallback;
use Bytes\DiscordClientBundle\Tests\Command\TestSlashCommand;
use Bytes\DiscordResponseBundle\Enums\Permissions;
use Symfony\Component\Console\Command\Command;
use ValueError;

class ConfigurationTest extends TestSlashCommand
{
    /**
     * @var string
     */
    protected $command = 'bytes_discord_client:slash:add';

    /**
     * @return void
     */
    public function testConfigurationViaCommandSuccess()
    {
        $commandTester = $this->setupCommandTester(MockSuccessfulAddCallback::class, array('1', '1'), ['endpoints' => ['user' => [
            'permissions' => [
                'add' => [
                    Permissions::ADD_REACTIONS->value,
                    Permissions::ATTACH_FILES->name,
                ]
            ]
        ]]]);

        $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    /**
     * @group success
     */
    public function testConfigurationViaCommandInvalid()
    {
        self::expectException(ValueError::class);
        $commandTester = $this->setupCommandTester(MockSuccessfulAddCallback::class, array('1', '1'), ['endpoints' => ['user' => [
            'permissions' => [
                'add' => [
                    Permissions::ADD_REACTIONS->value,
                    'abc',
                ]
            ]
        ]]]);

        $commandTester->execute([]);
    }
}
