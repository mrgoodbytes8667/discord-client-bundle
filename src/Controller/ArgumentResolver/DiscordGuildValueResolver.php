<?php

namespace Bytes\DiscordClientBundle\Controller\ArgumentResolver;

use Bytes\DiscordClientBundle\Attribute\MapGuild;
use Bytes\DiscordClientBundle\HttpClient\Api\DiscordBotClient;
use Bytes\DiscordResponseBundle\Objects\Guild;
use Bytes\DiscordResponseBundle\Objects\Interfaces\GuildInterface;
use Bytes\DiscordResponseBundle\Objects\PartialGuild;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use function is_int;
use function is_string;

/**
 * Converts and hydrates a Discord GuildInterface (Guild by default, can also deserialize into PartialGuild though
 * the permission field will always be null due to this hydrating via the bot endpoint)
 */
class DiscordGuildValueResolver implements ValueResolverInterface
{
    public function __construct(private DiscordBotClient $client)
    {
    }

    /**
     * Returns the possible value(s).
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!is_subclass_of($argument->getType(), GuildInterface::class)) {
            return [];
        }

        if ($argument->isVariadic()) {
            // only target route path parameters, which cannot be variadic.
            return [];
        }

        // do not support if no value can be resolved at all
        // letting the \Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver be used
        // or \Symfony\Component\HttpKernel\Controller\ArgumentResolver fail with a meaningful error.
        if (!$request->attributes->has($argument->getName())) {
            return [];
        }

        $value = $request->attributes->get($argument->getName());

        if (null === $value) {
            return [null];
        }

        if ($value instanceof PartialGuild) {
            return [$value];
        }

        if (!is_int($value) && !is_string($value)) {
            throw new LogicException(sprintf('Could not resolve the "%s $%s" controller argument: expecting an int or string, got "%s".', $argument->getType(), $argument->getName(), get_debug_type($value)));
        }

        if (is_a($argument->getType(), Guild::class, allow_string: true)) {
            $class = Guild::class;
        } else {
            $class = PartialGuild::class;
        }

        $withCounts = false;
        if ($attributes = $argument->getAttributes(MapGuild::class, ArgumentMetadata::IS_INSTANCEOF)) {
            /** @var MapGuild $attribute */
            $attribute = $attributes[0];
            $withCounts = $attribute->withCounts;
        }

        $instance = new $class();

        /** @var PartialGuild|Guild $instance */
        $instance->setId($value);

        try {
            $response = $this->client->getGuild($instance, $withCounts);
            if (!$response->isSuccess()) {
                throw new NotFoundHttpException(sprintf('Could not resolve the "%s $%s" controller argument: ', $argument->getType(), $argument->getName()));
            }
            $guild = $response
                ->deserialize(type: $class);
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface|BadRequestHttpException $e) {
            throw new NotFoundHttpException(sprintf('Could not resolve the "%s $%s" controller argument: ', $argument->getType(), $argument->getName()) . $e->getMessage(), $e);
        }

        return [$guild];
    }
}
