<?php

namespace Bytes\DiscordClientBundle\Event;

use Bytes\DiscordResponseBundle\Objects\Interfaces\ApplicationCommandInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 *
 */
abstract class AbstractApplicationCommandEvent extends Event
{
    /**
     * @param ApplicationCommandInterface|null $applicationCommand
     */
    public function __construct(private ?ApplicationCommandInterface $applicationCommand = null)
    {
    }

    /**
     * @param ApplicationCommandInterface|null $applicationCommand
     * @return static
     */
    public static function new(?ApplicationCommandInterface $applicationCommand = null): static
    {
        return new static($applicationCommand);
    }

    /**
     * @return ApplicationCommandInterface|null
     */
    public function getApplicationCommand(): ?ApplicationCommandInterface
    {
        return $this->applicationCommand;
    }

    /**
     * @param ApplicationCommandInterface|null $applicationCommand
     * @return $this
     */
    public function setApplicationCommand(?ApplicationCommandInterface $applicationCommand): self
    {
        $this->applicationCommand = $applicationCommand;
        return $this;
    }
}