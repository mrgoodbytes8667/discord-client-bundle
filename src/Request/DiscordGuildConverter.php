<?php


namespace Bytes\DiscordBundle\Request;


use Bytes\DiscordBundle\Services\Client\DiscordBot;
use Bytes\DiscordResponseBundle\Objects\Guild;
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
 * Converts and hydrates a Discord Guild
 * Due to the time necessary to communicate with the Discord API, this converter is disabled by default
 * @package Bytes\DiscordBundle\Request
 *
 * @link https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
 */
class DiscordGuildConverter implements ParamConverterInterface
{
    /**
     * @var DiscordBot
     */
    private DiscordBot $client;

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

        $class = $configuration->getClass();

        /** @var Guild $instance */
        $instance = new $class();
        if (!$instance instanceof Guild) {
            return false;
        }
        $instance->setId($value);

        try {
            $guild = $this->client->getGuild($instance);
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

    /**
     * DiscordGuildConverter constructor.
     * @param DiscordBot $client
     */
    public function __construct(DiscordBot $client)
    {
        $this->client = $client;
    }


}