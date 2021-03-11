<?php


namespace Bytes\DiscordBundle\Tests\Fixtures\Commands;

use Bytes\DiscordBundle\Slash\SlashCommandInterface;
use Bytes\DiscordResponseBundle\Enums\ApplicationCommandOptionType as ACOT;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOption as Option;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommandOptionChoice;


class Sample implements SlashCommandInterface
{
    /**
     * @return ApplicationCommand
     */
    public static function createCommand(): ApplicationCommand
    {
        return ApplicationCommand::create('sample', 'I am a sample command', [
            Option::create(ACOT::string(), 'Pick', 'Which is the word sample?', false, [
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
        return 'sample';
    }

}