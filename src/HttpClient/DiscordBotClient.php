<?php


namespace Bytes\DiscordBundle\HttpClient;


use Bytes\DiscordBundle\Services\Client\DiscordBot;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Slash\ApplicationCommand;
use Bytes\ResponseBundle\Enums\HttpMethods;
use Symfony\Component\HttpClient\Retry\RetryStrategyInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class DiscordBotClient
 * HttpClient portion of the Discord Bot API classes.
 * @package Bytes\DiscordBundle\HttpClient
 *
 * @see DiscordBot
 */
class DiscordBotClient extends DiscordClient
{
    /**
     * DiscordBotClient constructor.
     * @param HttpClientInterface $httpClient
     * @param RetryStrategyInterface|null $strategy
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param string $clientId
     * @param string $clientSecret
     * @param string $botToken
     * @param string|null $userAgent
     * @param array $defaultOptionsByRegexp
     * @param string|null $defaultRegexp
     */
    public function __construct(HttpClientInterface $httpClient, ?RetryStrategyInterface $strategy, ValidatorInterface $validator, SerializerInterface $serializer, string $clientId, string $clientSecret, string $botToken, ?string $userAgent, array $defaultOptionsByRegexp = [], string $defaultRegexp = null)
    {
        $defaultOptionsByRegexp = array_merge_recursive([
            // Matches non-oauth API routes
            DiscordClient::SCOPE_API => [
                'headers' => [
                    'Authorization' => 'Bot ' . $botToken,
                ],
            ],
        ], $defaultOptionsByRegexp);
        parent::__construct($httpClient, $strategy, $validator, $serializer, $clientId, $clientSecret, $botToken, $userAgent, $defaultOptionsByRegexp, $defaultRegexp);
    }

    /**
     * @param ApplicationCommand $applicationCommand
     * @param IdInterface|null $guild
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function createCommand(ApplicationCommand $applicationCommand, ?IdInterface $guild = null)
    {
        $edit = false;
        $errors = $this->validator->validate($applicationCommand);
        if (count($errors) > 0) {
            throw new ValidatorException((string)$errors);
        }

        $urlParts = ['applications', $this->clientId];

        if (!empty($guild)) {
            $urlParts[] = 'guilds';
            $urlParts[] = $guild->getId();
        }
        $urlParts[] = 'commands';

        if (!empty($applicationCommand->getId())) {
            $edit = true;
            $urlParts[] = $applicationCommand->getId();
        }

        $body = $this->serializer->serialize($applicationCommand, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        return $this->request($urlParts, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
        ], $edit ? HttpMethods::patch() : HttpMethods::post());
    }

    /**
     * @param ApplicationCommand $applicationCommand
     * @param IdInterface|null $guild
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function deleteCommand($applicationCommand, ?IdInterface $guild = null)
    {
        $commandId = $this->normalizeApplicationCommand($applicationCommand);
        $urlParts = ['applications', $this->clientId];

        if (!empty($guild)) {
            $urlParts[] = 'guilds';
            $urlParts[] = $guild->getId();
        }
        $urlParts[] = 'commands';
        $urlParts[] = $commandId;

        return $this->request($urlParts, [], HttpMethods::delete());
    }

    /**
     * @param IdInterface|null $guild
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function getCommands(?IdInterface $guild = null)
    {
        $urlParts = ['applications', $this->clientId];

        if (!empty($guild)) {
            $urlParts[] = 'guilds';
            $urlParts[] = $guild->getId();
        }
        $urlParts[] = 'commands';

        return $this->request($urlParts);
    }

    /**
     * @param $applicationCommand
     * @param IdInterface|null $guild
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function getCommand($applicationCommand, ?IdInterface $guild = null)
    {
        $commandId = $this->normalizeApplicationCommand($applicationCommand);
        $urlParts = ['applications', $this->clientId];

        if (!empty($guild)) {
            $urlParts[] = 'guilds';
            $urlParts[] = $guild->getId();
        }
        $urlParts[] = 'commands';
        $urlParts[] = $commandId;

        return $this->request($urlParts);
    }

    /**
     * @param ApplicationCommand|string $applicationCommand
     * @return string
     */
    protected function normalizeApplicationCommand($applicationCommand)
    {
        $commandId = '';
        if (is_null($applicationCommand)) {
            throw new BadRequestHttpException('The applicationCommand argument is required.');
        }
        if ($applicationCommand instanceof IdInterface) {
            $commandId = $applicationCommand->getId();
        } elseif (is_string($applicationCommand)) {
            $commandId = $applicationCommand;
        }
        if (empty($commandId)) {
            throw new BadRequestHttpException('The applicationCommand argument is required.');
        }
        return $commandId;
    }

    /**
     * Get Guild
     * Returns the guild object for the given id. If with_counts is set to true, this endpoint will also return approximate_member_count and approximate_presence_count for the guild.
     * @param $guild
     * @param bool $withCounts
     *
     * @return ResponseInterface
     *
     * @throws TransportExceptionInterface
     *
     * @link https://discord.com/developers/docs/resources/guild#get-guild
     */
    public function getGuild($guild, bool $withCounts = false)
    {
        $id = '';
        if(empty($guild))
        {
            throw new BadRequestHttpException('Guild cannot be blank.');
        } elseif ($guild instanceof IdInterface) {
            $id = $guild->getId();
        } elseif (is_string($guild)) {
            $id = $guild;
        } else {
            throw new BadRequestHttpException('Guild cannot be blank.');
        }
        $url = $this->buildURL(implode('/', ['guilds', $id]), 'v8');
        return $this->request($url, [
            'query' => [
                'with_counts' => $withCounts
            ]
        ]);
    }
}