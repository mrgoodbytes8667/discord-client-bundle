<?php


namespace Bytes\DiscordClientBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Trait DiscordUserTrait
 * @package Bytes\DiscordClientBundle\Entity
 */
trait DiscordUserTrait
{
    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private $discord_id;

    /**
     * @return string|null
     */
    public function getDiscordId(): ?string
    {
        return $this->discord_id;
    }

    /**
     * @param string|null $discord_id
     * @return $this
     */
    public function setDiscordId(?string $discord_id): self
    {
        $this->discord_id = $discord_id;

        return $this;
    }
}