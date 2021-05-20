<?php


namespace Bytes\DiscordClientBundle\Slash;


use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;

/**
 * Interface SlashCommandInterface
 * @package Bytes\DiscordClientBundle\Slash
 */
interface SlashCommandInterface
{
    /**
     * @return ApplicationCommand
     */
    public static function createCommand(): ApplicationCommand;

    /**
     * Return the command name
     * This should match the first argument into ApplicationCommand::create()
     * @return string
     */
    public static function getDefaultIndexName(): string;
}