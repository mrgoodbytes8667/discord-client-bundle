<?php

namespace Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient;

use Bytes\Common\Faker\Discord\TestDiscordFakerTrait;
use Bytes\DiscordClientBundle\Tests\Fixtures\Commands\Sample;
use Bytes\DiscordResponseBundle\Objects\Interfaces\ApplicationCommandInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildIdInterface;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandPermission;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Generator;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Enums\ApplicationCommandPermissionType;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Trait CommandPermissionsProviderTrait
 * @package Bytes\DiscordClientBundle\Tests\HttpClient\DiscordBotClient
 */
trait CommandPermissionsProviderTrait
{
    use TestDiscordFakerTrait, GuildProviderTrait;

    /**
     * @return Generator
     */
    public function provideGuildCommand()
    {
        $this->setupFaker();
        foreach ($this->provideValidGuilds() as $guild) {
            $command = Sample::createCommand();
            $command->setId($this->faker->snowflake());
            yield ['command' => $command, 'guild' => $guild[0]];

            $command = $this
                ->getMockBuilder(ApplicationCommandInterface::class)
                ->getMock();
            $command->method('getCommandId')
                ->willReturn($this->faker->snowflake());
            yield ['command' => $command, 'guild' => $guild[0]];

            $command = $this
                ->getMockBuilder(IdInterface::class)
                ->getMock();
            $command->method('getId')
                ->willReturn($this->faker->snowflake());
            yield ['command' => $command, 'guild' => $guild[0]];
        }
    }

    /**
     * @return Generator
     */
    public function providePermission()
    {
        $this->setupFaker();
        $roleOrUser = $this->faker->snowflake();
        $type = $this->faker->applicationCommandPermissionType();
        $allow = $this->faker->boolean();

        $permission = ApplicationCommandPermission::create($roleOrUser, $type, $allow);

        yield ['roleOrUser' => $roleOrUser, 'type' => $type, 'allow' => $allow, 'permission' => $permission];
    }

    /**
     * @return Generator
     */
    #[ArrayShape(['command' => ApplicationCommand::class, 'guild' => GuildIdInterface::class, 'roleOrUser' => 'string', 'type' => ApplicationCommandPermissionType::class, 'allow' => 'bool', 'permission' => ApplicationCommandPermission::class])]
    public function provideGuildCommandPermission()
    {
        $this->setupFaker();
        foreach ($this->provideGuildCommand() as $item) {
            foreach ($this->providePermission() as $p) {
                //$test = array_merge($item, $p);
                yield array_merge($item, $p);
            }
        }
    }
}