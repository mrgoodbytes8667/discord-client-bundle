<?php

namespace Bytes\DiscordClientBundle\Event;

use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;

/**
 *
 */
class ApplicationCommandDeletedEvent extends AbstractApplicationCommandEvent
{
    /**
     * @param string $commandId
     * @return static
     */
    public static function setCommandId(string $commandId): static
    {
        $command = new ApplicationCommand();
        $command->setId($commandId);
        return new static($command);
    }
}