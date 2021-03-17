<?php


namespace Bytes\DiscordBundle\Controller;


use Bytes\DiscordBundle\HttpClient\DiscordBotClient;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class CommandController
 * @package Bytes\DiscordBundle\Controller
 */
class CommandController
{

    /**
     * @var DiscordBotClient
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * CommandController constructor.
     * @param DiscordBotClient $client
     * @param SerializerInterface $serializer
     */
    public function __construct(DiscordBotClient $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @param PartialGuild|null $guild
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function add(Request $request, ?PartialGuild $guild = null)
    {
        $content = $request->getContent();
        if (empty($content)) {
            throw new BadRequestHttpException('Command is required');
        }

        $command = $this->serializer->deserialize($content, ApplicationCommand::class, 'json', [UnwrappingDenormalizer::UNWRAP_PATH => '[command]']);

        $response = $this->client->createCommand($command, $guild);

        $code = $response->getStatusCode();

        $createdCommand = $this->serializer->deserialize($response->getContent(), ApplicationCommand::class, 'json');
        $guildId = null;
        if (!is_null($guild)) {
            $guildId = $guild->getId();
        }

        $return = [
            'status_code' => $code,
            'guild' => $guildId,
            'message' => $code == 201 ? 'Command created successfully' : ($code == 200 ? 'Command edited successfully' : 'Status code does not correspond to a known method'),
            'command' => $createdCommand,
        ];

        $serialized = $this->serializer->serialize($return, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $response = new JsonResponse();
        $response->setJson($serialized);
        $response->setStatusCode($code);
        $response->setCache(['no_cache' => true]);

        return $response;
    }

    /**
     * @param ApplicationCommand $command
     * @param PartialGuild|null $guild
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function delete(ApplicationCommand $command, ?PartialGuild $guild = null)
    {
        $response = $this->client->deleteCommand($command, $guild);

        $code = $response->getStatusCode();

        // Forces a throw if failed
        $response->getContent();

        $guildId = null;
        if (!is_null($guild)) {
            $guildId = $guild->getId();
        }

        $return = [
            'status_code' => $code,
            'guild' => $guildId,
            'message' => 'Command deleted successfully',
            'command' => null,
        ];

        $serialized = $this->serializer->serialize($return, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $response = new JsonResponse();
        $response->setJson($serialized);
        $response->setStatusCode($code);
        $response->setCache(['no_cache' => true]);

        return $response;
    }

    /**
     * @param PartialGuild|null $guild
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function list(?PartialGuild $guild = null)
    {
        $response = $this->client->getCommandsResponse($guild);

        $code = $response->getStatusCode();

        $commands = $this->serializer->deserialize($response->getContent(), '\Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand[]', 'json');

        $guildId = null;
        if (!is_null($guild)) {
            $guildId = $guild->getId();
        }

        $return = [
            'status_code' => $code,
            'guild' => $guildId,
            'message' => sprintf('Found %d command%s', count($commands), count($commands) != 1 ? 's' : ''),
            'commands' => $commands,
        ];

        $serialized = $this->serializer->serialize($return, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $response = new JsonResponse();
        $response->setJson($serialized);
        $response->setStatusCode($code);
        $response->setCache(['no_cache' => true]);

        return $response;
    }

    /**
     * @param PartialGuild|null $guild
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function show(ApplicationCommand $command, ?PartialGuild $guild = null)
    {
        $response = $this->client->getCommandResponse($command, $guild);

        $code = $response->getStatusCode();

        $result = $this->serializer->deserialize($response->getContent(), ApplicationCommand::class, 'json');

        $guildId = null;
        if (!is_null($guild)) {
            $guildId = $guild->getId();
        }

        $return = [
            'status_code' => $code,
            'guild' => $guildId,
            'message' => 'Found command',
            'command' => $result,
        ];

        $serialized = $this->serializer->serialize($return, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $response = new JsonResponse();
        $response->setJson($serialized);
        $response->setStatusCode($code);
        $response->setCache(['no_cache' => true]);

        return $response;
    }
}