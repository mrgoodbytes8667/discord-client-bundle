<?php


namespace Bytes\DiscordClientBundle\Slash;


use Bytes\DiscordResponseBundle\Enums\Permissions;
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
     * This *MUST* match the first argument into ApplicationCommand::create()
     * @return string
     */
    public static function getDefaultIndexName(): string;

    /**
     * @return bool False will prevent the permissions command from displaying the @everyone role.
     */
    public static function allowEveryoneRole(): bool;

    /**
     * For future use:
     * Returns an array of Discord Permission enums that will be used by the add command to build a list of roles to get
     * access to run the command. Any role that has any permission in this list will get access.
     * @return Permissions[]
     */
    public static function getPermissionsForRoles(): array;
}