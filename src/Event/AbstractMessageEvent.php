<?php

namespace Bytes\DiscordClientBundle\Event;

use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\ResponseBundle\Event\EventTrait;
use Bytes\ResponseBundle\Event\PersistTrait;
use Exception;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 *
 */
abstract class AbstractMessageEvent extends Message implements StoppableEventInterface
{
    use EventTrait, PersistTrait;

    /**
     * @var
     */
    private $entityId;

    /**
     * @param Message|null $message
     * @return static
     * @throws Exception
     */
    public static function createFromMessage(?Message $message): static
    {
        $static = new static();
        if (empty($message)) {
            return $static;
        }

        $static->setAuthor($message->getAuthor())
            ->setMember($message->getMember())
            ->setContent($message->getContent())
            ->setTimestamp($message->getTimestamp())
            ->setEditedTimestamp($message->getEditedTimestamp())
            ->setTts($message->getTts())
            ->setMentionEveryone($message->getMentionEveryone())
            ->setMentionRoles($message->getMentionRoles())
            ->setMentionChannels($message->getMentionChannels())
            ->setEmbeds($message->getEmbeds())
            ->setReactions($message->getReactions())
            ->setNonce($message->getNonce())
            ->setPinned($message->getPinned())
            ->setWebhookId($message->getWebhookId())
            ->setType($message->getType())
            ->setMessageReference($message->getMessageReference())
            ->setFlags($message->getFlags())
            ->setReferencedMessage($message->getReferencedMessage())
            ->setInteraction($message->getInteraction())
            ->setThread($message->getThread())
            ->setComponents($message->getComponents())
            ->setStickerItems($message->getStickerItems())
            ->setId($message->getId())
            ->setGuildId($message->getGuildId())
            ->setMessage($message->getMessage())
            ->setCode($message->getCode())
            ->setRetryAfter($message->getRetryAfter())
            ->setGlobal($message->getGlobal())
            ->setChannelID($message->getChannelID());

        return $static;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param $entityId
     * @return $this
     */
    public function setEntityId($entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }
}
