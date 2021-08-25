<?php


namespace Bytes\DiscordClientBundle\Request;


use Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordResponseBundle\Exceptions\DiscordClientException;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class DiscordGuildConverter
 * Converts and hydrates a Discord GuildInterface (Guild by default, can also deserialize into PartialGuild)
 * @package Bytes\DiscordClientBundle\Request
 *
 * @link https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
 *
 * Due to the time necessary to communicate with the Discord API, this converter is disabled by default
 * To use this converter, add the @ParamConverter() tag.
 *
 * <code>
 * // Using a route with a guild param...
 * @Route("/some/route/to/{guild}")
 * // Use the DiscordGuildConverter
 * @ParamConverter("guild", converter="bytes_discord_client_guild")
 * // Use the DiscordGuildConverter and deserialize into Guild, same result as above
 * @ParamConverter("guild", converter="bytes_discord_client_guild", options={"class" = "\Bytes\DiscordResponseBundle\Objects\Guild"})
 * // Use the DiscordGuildConverter and include counts
 * @ParamConverter("guild", converter="bytes_discord_client_guild", options={"with_counts" = true})
 * // Use the DiscordGuildConverter and deserialize into PartialGuild
 * @ParamConverter("guild", converter="bytes_discord_client_guild", options={"class" = "\Bytes\DiscordResponseBundle\Objects\PartialGuild"})
 * // Use the DiscordGuildConverter and deserialize into PartialGuild and include counts
 * @ParamConverter("guild", converter="bytes_discord_client_guild", options={"class" = "\Bytes\DiscordResponseBundle\Objects\PartialGuild", "with_counts" = true})
 * </code>
 */
class DiscordGuildConverter implements ParamConverterInterface
{
    const OPTIONS_CLASS = 'class';
    const OPTIONS_WITH_COUNTS = 'with_counts';

    /**
     * DiscordGuildConverter constructor.
     * @param DiscordBotClient $client
     */
    public function __construct(public DiscordBotClient $client)
    {
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();

        if (!$request->attributes->has($param)) {
            return false;
        }

        $value = $request->attributes->get($param);

        if (!$value && $configuration->isOptional()) {
            $request->attributes->set($param, null);

            return true;
        }

        $options = $configuration->getOptions();
        $withCounts = false;
        $deserializeInto = Guild::class;
        if (isset($options[self::OPTIONS_WITH_COUNTS])) {
            if ($options[self::OPTIONS_WITH_COUNTS] === true) {
                $withCounts = true;
            }
        }
        if (isset($options[self::OPTIONS_CLASS]) && is_subclass_of($options[self::OPTIONS_CLASS], GuildInterface::class)) {
            $deserializeInto = $options[self::OPTIONS_CLASS];
        }

        $class = $configuration->getClass();

        /** @var Guild $instance */
        $instance = new $class();
        if (!$instance instanceof Guild) {
            return false;
        }
        $instance->setId($value);

        try {
            $response = $this->client->getGuild($instance, $withCounts);
            if(!$response->isSuccess())
            {
                return false;
            }
            $guild = $response
                ->deserialize(type: $deserializeInto);
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface | BadRequestHttpException $exception) {
            return false;
        }

        $request->attributes->set($param, $guild);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        return $configuration->getClass() === Guild::class;
    }
}