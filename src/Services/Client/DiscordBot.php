<?php


namespace Bytes\DiscordBundle\Services\Client;


use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordResponseBundle\Objects\Channel;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildIdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\DiscordResponseBundle\Objects\User;
use Bytes\DiscordResponseBundle\Services\IdNormalizer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class DiscordBot
 * @package Bytes\DiscordBundle\Services\Client
 *
 * @method ResponseInterface createCommand(ApplicationCommand $applicationCommand, ?IdInterface $guild = null)
 * @method ResponseInterface deleteCommand(ApplicationCommand $applicationCommand, ?IdInterface $guild = null)
 */
class DiscordBot
{
    use SharedGetMethodsTrait;

    /**
     * @var DiscordBotClient
     */
    private DiscordBotClient $client;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * DiscordBot constructor.
     * @param DiscordBotClient $client
     * @param SerializerInterface $serializer
     */
    public function __construct(DiscordBotClient $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'createCommand':
                return $this->client->createCommand(...$arguments);
                break;
            case 'deleteCommand':
                return $this->client->deleteCommand(...$arguments);
                break;
        }
        throw new BadRequestHttpException();
    }


    /**
     * @param IdInterface|null $guild
     *
     * @return ApplicationCommand[]|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws BadRequestHttpException
     */
    public function getCommands(?IdInterface $guild = null)
    {
        $response = $this->client->getCommands($guild);
        $content = $response->getContent();
        return $this->serializer->deserialize($content, 'Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand[]', 'json');
    }

    /**
     * @param ApplicationCommand|string $applicationCommand
     * @param IdInterface|null $guild
     *
     * @return ApplicationCommand|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws BadRequestHttpException
     */
    public function getCommand($applicationCommand, ?IdInterface $guild = null)
    {
        $response = $this->client->getCommand($applicationCommand, $guild);
        $content = $response->getContent();
        return $this->serializer->deserialize($content, ApplicationCommand::class, 'json');
    }

    /**
     * Get Guild
     * Returns the guild object for the given id. If with_counts is set to true, this endpoint will also return approximate_member_count and approximate_presence_count for the guild.
     *
     * @param IdInterface|string $guild
     * @param bool $withCounts
     * @param array $context
     * @param string $class = [PartialGuild::class, Guild::class][$any]
     * @return Guild|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @link https://discord.com/developers/docs/resources/guild#get-guild
     */
    public function getGuild($guild, bool $withCounts = false, array $context = [], string $class = Guild::class)
    {
        $response = $this->client->getGuild($guild, $withCounts);
        $content = $response->getContent();
        return $this->serializer->deserialize($content, $class, 'json', $context);
    }

    /**
     * Get User
     * Returns a user object for a given user ID.
     * @param IdInterface|string $userId
     * @param array $context
     *
     * @return User|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/user#get-user
     */
    public function getUser($userId, array $context = [])
    {
        $response = $this->client->getUser($userId);
        $content = $response->getContent();
        return $this->serializer->deserialize($content, User::class, 'json', $context);
    }

    /**
     * Get Guild Channels
     * Returns a list of guild channel objects.
     * @param GuildIdInterface|IdInterface|string $guildId
     * @param array $context
     *
     * @return Channel[]|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/guild#get-guild-channels
     */
    public function getChannels($guildId, array $context = [])
    {
        $response = $this->client->getChannels($guildId);
        $content = $response->getContent();
        return $this->serializer->deserialize($content, '\Bytes\DiscordResponseBundle\Objects\Channel[]', 'json', $context);
    }

    /**
     * Get Channel
     * Get a channel by ID. Returns a channel object.
     * @param IdInterface|string $channelId
     * @param array $context
     *
     * @return Channel|null
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getChannel($channelId, array $context = [])
    {
        $response = $this->client->getChannel($channelId);
        $content = $response->getContent();
        return $this->serializer->deserialize($content, Channel::class, 'json', $context);
    }
}