<?php


namespace Bytes\DiscordBundle\Services\Client;


use Bytes\DiscordBundle\HttpClient\DiscordUserClient;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class DiscordUser
 * @package Bytes\DiscordBundle\Services\Client
 */
class DiscordUser
{
    use SharedGetMethodsTrait;

    /**
     * @var DiscordUserClient
     */
    private DiscordUserClient $client;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * DiscordUser constructor.
     * @param DiscordUserClient $client
     * @param SerializerInterface $serializer
     */
    public function __construct(DiscordUserClient $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }
}