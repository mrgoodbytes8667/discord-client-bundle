<?php


namespace Bytes\DiscordClientBundle\Request;


use Bytes\DiscordResponseBundle\Objects\Channel;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildIdInterface;
use Bytes\DiscordResponseBundle\Objects\Interfaces\NameInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use Bytes\DiscordResponseBundle\Objects\Role;
use Bytes\ResponseBundle\Interfaces\IdInterface;
use Illuminate\Support\Arr;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use function Symfony\Component\String\u;

/**
 * Class DiscordConverter
 * Converts various discord response objects with a name, id, or guild_id that implements IdInterface, GuildIdInterface,
 * or NameInterface. Parameters must be named name or guild_id for NameInterface or GuildIdInterface, otherwise it will
 * fall back to IdInterface.
 * @package Bytes\DiscordClientBundle\Request
 *
 * @link https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
 */
class DiscordConverter implements ParamConverterInterface
{
    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     * @link https://php.net/manual/en/language.oop5.decon.php
     */
    public function __construct(protected PropertyInfoExtractorInterface $extractor, protected PropertyAccessorInterface $accessor)
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

        $class = $configuration->getClass();

        $instance = new $class();
        if (is_subclass_of($class, NameInterface::class) && $param === 'name') {
            $instance->setName($value);
        } elseif (is_subclass_of($instance, Role::class) || $instance instanceof Role) {
            $instance->setId($value);
            $this->copyArray($request->attributes->all(), $instance, prefix: 'role', mappings: ['role_id' => 'id', 'roleId' => 'id', 'guild_id' => 'guild', 'guild' => 'guild'], skips: ['id']);
            $this->copyArray($request->query->all(), $instance, prefix: 'role', mappings: ['role_id' => 'id', 'roleId' => 'id', 'guild_id' => 'guild', 'guild' => 'guild'], skips: ['id']);
        } elseif (is_subclass_of($instance, Channel::class) || $instance instanceof Channel) {
            $instance->setId($value);
            $this->copyArray($request->attributes->all(), $instance, prefix: 'channel', mappings: ['channel_id' => 'id', 'channelId' => 'id', 'guild_id' => 'guildId', 'guild' => 'guildId'], skips: ['id']);
        } elseif (is_subclass_of($instance, PartialGuild::class) || $instance instanceof PartialGuild) {
            $instance->setId($value);
            $this->copyArray($request->attributes->all(), $instance, prefix: 'guild', mappings: ['guild_id' => 'id', 'guildId' => 'id', 'guild' => 'id'], skips: ['id']);
        } elseif (is_subclass_of($class, GuildIdInterface::class) && in_array($param, ['guild_id', 'guildId'])) {
            $instance->setGuildId($value);
        } elseif (is_subclass_of($class, IdInterface::class)) {
            $instance->setId($value);
        } else {
            return false;
        }

        $request->attributes->set($param, $instance);

        return true;
    }

    /**
     * @param array $source
     * @param object|array $destination The object or array to modify
     * @param string $prefix
     * @param array|null $mappings
     * @param array|null $skips
     * @return object|array
     */
    public function copyArray(array $source, object|array $destination, string $prefix, ?array $mappings = [], ?array $skips = []): object|array
    {
        $properties = array_keys($source);
        $properties = Arr::where($properties, function ($value) use ($prefix, $skips) {
            return !in_array($value, $skips) && u($value)->startsWith($prefix);
        });
        foreach ($properties as $property) {
            $prop = u($property)->snake()->replace($prefix . '_', '');
            foreach ([$property, $prop->camel()->toString(), $prop->toString()] as $v) {
                if ($this->extractor->isWritable($destination::class, $v)) {
                    $this->accessor->setValue($destination, $v, $source[$property]);
                }
            }
        }
        $mappings = Arr::where($mappings, function ($value, $key) use ($source) {
            return array_key_exists($key, $source);
        });
        foreach ($mappings as $sourceProperty => $destinationProperty) {
            if ($this->extractor->isWritable($destination::class, $destinationProperty)) {
                $this->accessor->setValue($destination, $destinationProperty, $source[$sourceProperty]);
            }
        }

        return $destination;
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