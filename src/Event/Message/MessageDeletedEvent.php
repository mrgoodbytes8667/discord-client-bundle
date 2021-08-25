<?php

namespace Bytes\DiscordClientBundle\Event\Message;

use Bytes\DiscordClientBundle\Event\AbstractMessageEvent;

/**
 *
 */
class MessageDeletedEvent extends AbstractMessageEvent
{
    /**
     * @param string $messageId
     * @return static
     */
    public static function setMessageId(string $messageId): static
    {
        $static = new static();
        $static->setId($messageId);

        return $static;
    }
}