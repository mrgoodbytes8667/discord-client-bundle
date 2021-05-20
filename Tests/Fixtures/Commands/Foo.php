<?php


namespace Bytes\DiscordClientBundle\Tests\Fixtures\Commands;

use Bytes\DiscordClientBundle\Slash\SlashCommandInterface;
use Bytes\DiscordResponseBundle\Enums\ApplicationCommandOptionType as ACOT;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOption as Option;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOptionChoice;


class Foo implements SlashCommandInterface
{
    /**
     * @return ApplicationCommand
     */
    public static function createCommand(): ApplicationCommand
    {
        return ApplicationCommand::create('foo', 'I am a sample command', [
            Option::create(ACOT::string(), 'Pick', 'Which is the word foo?', false, [
                ApplicationCommandOptionChoice::create('Foo'),
                ApplicationCommandOptionChoice::create('Bar'),
                ApplicationCommandOptionChoice::create('Sample')
            ])
        ]);
    }

    /**
     * Return the command name
     * @return string
     */
    public static function getDefaultIndexName(): string
    {
        return 'foo';
    }

}