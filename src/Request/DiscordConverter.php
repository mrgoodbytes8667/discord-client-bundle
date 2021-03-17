<?php


namespace Bytes\DiscordBundle\Request;


use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildIdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\IdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\NameInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DiscordConverter
 * Converts various discord response objects with a name, id, or guild_id that implements IdInterface, GuildIdInterface,
 * or NameInterface. Parameters must be named name or guild_id for NameInterface or GuildIdInterface, otherwise it will
 * fall back to IdInterface.
 * @package Bytes\DiscordBundle\Request
 *
 * @link https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
 */
class DiscordConverter implements ParamConverterInterface
{
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

        $instance = new $class();
        if (is_subclass_of($class, GuildIdInterface::class) && in_array($param, ['guild_id', 'guildId'])) {
            $instance->setGuildId($value);
        } elseif (is_subclass_of($class, NameInterface::class) && $param === 'name') {
            $instance->setName($value);
        } elseif (is_subclass_of($class, IdInterface::class)) {
            $instance->setId($value);
        } else {
            return false;
        }

        $request->attributes->set($param, $instance);

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

        return is_subclass_of($configuration->getClass(), IdInterface::class) ||
            (is_subclass_of($configuration->getClass(), GuildIdInterface::class) && in_array($configuration->getName(), ['guild_id', 'guildId'])) ||
            (is_subclass_of($configuration->getClass(), NameInterface::class) && $configuration->getName() === 'name');
    }
}