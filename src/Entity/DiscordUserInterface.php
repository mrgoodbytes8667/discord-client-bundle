<?php


namespace Bytes\DiscordBundle\Entity;


/**
 * Interface DiscordUserInterface
 * @package Bytes\DiscordBundle\Entity
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