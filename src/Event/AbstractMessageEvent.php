<?php

namespace Bytes\DiscordClientBundle\Event;

use Bytes\DiscordResponseBundle\Objects\Message;
use Bytes\ResponseBundle\Event\EventTrait;
use Bytes\ResponseBundle\Event\PersistTrait;
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