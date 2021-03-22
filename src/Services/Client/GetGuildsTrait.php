<?php


namespace Bytes\DiscordBundle\Services\Client;


use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Trait GetGuildsTrait
 * @package Bytes\DiscordBundle\Services\Client
 *
 * @property $client
 * @property $serializer
 */
trait GetGuildsTrait
{
    /**
     * Get Current User Guilds
     * Returns a list of partial guild objects the current user is a member of. Requires the guilds OAuth2 scope.
     * This endpoint returns 100 guilds by default, which is the maximum number of guilds a non-bot user can join. Therefore, pagination is not needed for integrations that need to get a list of the users' guilds.
     *
     * @return PartialGuild[]|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-current-user-guilds
     */
    public function getGuilds()
    {
        $response = $this->client->getGuilds();
        $content = $response->getContent();
        return $this->serializer->deserialize($content, '\Bytes\DiscordResponseBundle\Objects\PartialGuild[]', 'json');
    }
}