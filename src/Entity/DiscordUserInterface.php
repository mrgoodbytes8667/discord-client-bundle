<?php


namespace Bytes\DiscordClientBundle\Entity;


/**
 * Interface DiscordUserInterface
 * @package Bytes\DiscordClientBundle\Entity
 */
interface DiscordUserInterface
{
    /**
     * @return string|null
     */
    public function getDiscordId(): ?string;

    /**
     * @param string|null $discord_id
     * @return $this
     */
    public function setDiscordId(?string $discord_id);
}